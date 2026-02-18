/**
 * API Client for handling HTTP requests
 * Provides a consistent interface for all API communications
 */
class ApiClient {
	/**
	 * Base URL for API requests
	 * @type {string}
	 */
	static baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';

	/**
	 * Default request timeout in milliseconds
	 * @type {number}
	 */
	static timeout = 30000;

	/**
	 * Perform an HTTP request
	 * @param {string} url - Request URL
	 * @param {Object} options - Request options
	 * @param {string} [options.method='GET'] - HTTP method
	 * @param {Object|FormData} [options.body] - Request body
	 * @param {Object} [options.headers={}] - Additional headers
	 * @param {number} [options.timeout] - Request timeout
	 * @returns {Promise<Object|string>} Response data
	 * @throws {ApiError} If request fails
	 */
	static async request(url, options = {}) {
		const csrfMeta = document.querySelector('meta[name="csrf-token"]');
		const defaultHeaders = {
			'X-Requested-With': 'XMLHttpRequest',
		};

		if (csrfMeta) {
			defaultHeaders['X-CSRF-Token'] = csrfMeta.content;
		}

		if (!(options.body instanceof FormData)) {
			defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		const config = {
			method: options.method || 'GET',
			headers: { ...defaultHeaders, ...options.headers },
		};

		if (options.body) {
			if (options.body instanceof FormData) {
				config.body = options.body;
			} else if (typeof options.body === 'object') {
				config.body = new URLSearchParams(options.body).toString();
			} else {
				config.body = options.body;
			}
		}

		// --- FIXED TIMEOUT & SIGNAL LINKING ---
		const timeout = options.timeout || this.timeout;
		const controller = new AbortController();
		const timeoutId = setTimeout(() => controller.abort(), timeout);

		// LINKING: If an external signal was provided (e.g. from EntityManager),
		// we listen for it. If it aborts, we abort our local controller too.
		if (options.signal) {
			if (options.signal.aborted) {
				controller.abort();
			} else {
				options.signal.addEventListener('abort', () => controller.abort());
			}
		}

		config.signal = controller.signal;
		// --------------------------------------

		try {
			const response = await fetch(url, config);
			clearTimeout(timeoutId);

			if (!response.ok) {
				let errorData = {};
				try {
					errorData = await this.parseResponse(response);
				} catch (e) {}

				const errorMessage =
					errorData.error ||
					errorData.message ||
					`HTTP Error: ${response.status}`;
				const apiError = new ApiError(
					errorMessage,
					response.status,
					errorData,
				);

				if (errorData.field) {
					apiError.field = errorData.field;
				}

				// Session expired — redirect to login
				if (response.status === 401) {
					window.location.href = SITE_URL + 'login';
					return;
				}

				throw apiError;
			}

			return await this.parseResponse(response);
		} catch (error) {
			clearTimeout(timeoutId);

			// If the error came from our linked signal, fetch will throw AbortError
			if (error.name === 'AbortError') {
				// If the external signal was the cause, re-throw it so EntityManager can catch it
				if (options.signal?.aborted) {
					throw error;
				}
				// Otherwise, it was our own internal timeout
				throw new ApiError('Request timeout', 408);
			}

			if (error instanceof ApiError) {
				throw error;
			}

			console.error('API Request Failed:', error);
			throw new ApiError('Network error occurred', 0, {
				originalError: error,
			});
		}
	}

	/**
	 * Parse response based on content type
	 * @param {Response} response - Fetch response object
	 * @returns {Promise<Object|string>} Parsed response
	 */
	static async parseResponse(response) {
		const contentType = response.headers.get('content-type');

		if (contentType && contentType.includes('application/json')) {
			return await response.json();
		}

		return await response.text();
	}

	/**
	 * Fetch rendered HTML from server
	 * Uses this.request() to ensure CSRF tokens, headers, and timeouts are included.
	 * * @param {string} url - Base URL
	 * @param {Object} params - Query parameters
	 * @returns {Promise<string>} Rendered HTML string
	 */
	static async fetchHtml(url, params = {}) {
		// 1. Use buildUrl to safely handle '?' vs '&' logic
		const fullUrl = this.buildUrl(url, params);

		// 2. Use the core request method
		// This automatically adds:
		// - X-CSRF-Token
		// - X-Requested-With
		// - Timeout signal
		// - Standard error handling (ApiError)
		return this.request(fullUrl, {
			method: 'GET',
			headers: {
				Accept: 'text/html', // Explicitly tell server we want HTML
			},
		});
	}

	/**
	 * Build URL with query parameters
	 * @param {string} baseUrl - Base URL
	 * @param {Object} params - Query parameters
	 * @returns {string} Complete URL
	 */
	static buildUrl(baseUrl, params = {}) {
		const queryString = new URLSearchParams(params).toString();

		// Handle both cases: URL already has params or not
		const separator = baseUrl.includes('?') ? '&' : '?';

		return queryString ? `${baseUrl}${separator}${queryString}` : baseUrl;
	}

	/**
	 * Build module URL
	 * @param {string} module - Module name
	 * @param {string} controller - Controller name
	 * @param {string} action - Action name
	 * @param {Object} params - Additional query parameters
	 * @returns {string} Complete URL
	 */
	static buildModuleUrl(module, controller, action, params = {}) {
		const baseParams = {
			module,
			controller,
			action,
			...params,
		};
		return this.buildUrl(this.baseUrl, baseParams);
	}

	/**
	 * Perform GET request
	 * @param {string} url - Request URL
	 * @param {Object} params - Query parameters
	 * @returns {Promise<Object|string>} Response data
	 */
	static get(url, params = {}) {
		const fullUrl = this.buildUrl(url, params);
		return this.request(fullUrl, { method: 'GET' });
	}

	/**
	 * Perform POST request
	 * @param {string} url - Request URL
	 * @param {Object|FormData} data - Request body
	 * @returns {Promise<Object>} Response data
	 */
	static post(url, data) {
		return this.request(url, { method: 'POST', body: data });
	}

	/**
	 * Perform PUT request
	 * @param {string} url - Request URL
	 * @param {Object|FormData} data - Request body
	 * @returns {Promise<Object>} Response data
	 */
	static put(url, data) {
		return this.request(url, { method: 'PUT', body: data });
	}

	/**
	 * Perform DELETE request
	 * @param {string} url - Request URL
	 * @param {Object} params - Query parameters
	 * @returns {Promise<Object>} Response data
	 */
	static delete(url, params = {}) {
		const fullUrl = this.buildUrl(url, params);
		return this.request(fullUrl, { method: 'DELETE' });
	}

	/**
	 * Upload file(s)
	 * @param {string} url - Upload URL
	 * @param {File|File[]} files - File(s) to upload
	 * @param {Object} additionalData - Additional form data
	 * @returns {Promise<Object>} Upload response
	 */
	static async uploadFiles(url, files, additionalData = {}) {
		const formData = new FormData();

		// Add files
		if (Array.isArray(files)) {
			files.forEach((file, index) => {
				formData.append(`files[${index}]`, file);
			});
		} else {
			formData.append('file', files);
		}

		// Add additional data
		Object.keys(additionalData).forEach((key) => {
			formData.append(key, additionalData[key]);
		});

		return this.post(url, formData);
	}
}

/**
 * Custom API Error class
 */
class ApiError extends Error {
	/**
	 * @param {string} message - Error message
	 * @param {number} status - HTTP status code
	 * @param {Object} data - Additional error data
	 */
	constructor(message, status = 0, data = {}) {
		super(message);
		this.name = 'ApiError';
		this.status = status;
		this.data = data;
	}

	/**
	 * Check if error is a validation error
	 * @returns {boolean}
	 */
	isValidationError() {
		return this.status === 422;
	}

	/**
	 * Check if error is not found
	 * @returns {boolean}
	 */
	isNotFound() {
		return this.status === 404;
	}

	/**
	 * Check if error is unauthorized
	 * @returns {boolean}
	 */
	isUnauthorized() {
		return this.status === 401 || this.status === 403;
	}

	/**
	 * Check if error is server error
	 * @returns {boolean}
	 */
	isServerError() {
		return this.status >= 500;
	}
}

// Make globally available
window.ApiClient = ApiClient;
window.ApiError = ApiError;
