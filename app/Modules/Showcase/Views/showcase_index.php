<?php
/**
 * Public showcase — a read-only "front of house" view of the collection.
 * No login required to browse; the Edit-in-Admin button (and the assets it needs)
 * only render for a visitor who is already signed in as an admin.
 *
 * @var string $collectionJson
 * @var string $universesJson
 * @var bool   $isAdmin
 * @var string $baseUrl
 */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>The Display Case</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<?php if ($isAdmin): ?>
<link href="<?= $e($baseUrl) ?>assets/css/app.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
<?php endif; ?>
<style>
:root{
  --paper:#EFEAE0; --paper-raised:#F8F4EB; --paper-sunken:#E4DED0;
  --ink:#221F27; --ink-soft:#5B5560; --ink-faint:#8B8590;
  --line:rgba(34,31,39,0.15); --line-strong:rgba(34,31,39,0.28);
  --brass:#8C6A26; --brass-bright:#B08A3A; --brass-ink:#6E521C;
  --shadow:rgba(34,31,39,0.20); --scrim:rgba(21,20,26,0.55);
  --good:#3E6F4E; --warn:#A6631E; --pending:#5A5F86;

  --accent-1a:#24397A; --accent-1b:#5E6FBE; --accent-1g:#7C8CE0;
  --accent-2a:#3E4A1F; --accent-2b:#7B6A2E; --accent-2g:#C77F2E;
  --accent-3a:#5B2E7E; --accent-3b:#9A4F2C; --accent-3g:#E8792C;

  --font-display:'Big Shoulders Display', system-ui, sans-serif;
  --font-body:'Fraunces', Georgia, 'Times New Roman', serif;
  --font-mono:'IBM Plex Mono', ui-monospace, 'SFMono-Regular', monospace;
}
@media (prefers-color-scheme: dark){
  :root:not([data-theme="light"]){
    --paper:#151319; --paper-raised:#1E1C24; --paper-sunken:#0F0E13;
    --ink:#EDE8DC; --ink-soft:#B7B0BF; --ink-faint:#77717F;
    --line:rgba(237,232,220,0.13); --line-strong:rgba(237,232,220,0.24);
    --brass:#D6AE58; --brass-bright:#E8C878; --brass-ink:#F0D68E;
    --shadow:rgba(0,0,0,0.55); --scrim:rgba(6,6,9,0.65);
    --good:#7FBE94; --warn:#E0A85C; --pending:#9AA0D6;
    --accent-1a:#141E45; --accent-1b:#3A4C9E; --accent-1g:#8DA0F5;
    --accent-2a:#232B10; --accent-2b:#5A4E1F; --accent-2g:#E29A44;
    --accent-3a:#341A49; --accent-3b:#6E331A; --accent-3g:#F5934A;
  }
}
:root[data-theme="dark"]{
  --paper:#151319; --paper-raised:#1E1C24; --paper-sunken:#0F0E13;
  --ink:#EDE8DC; --ink-soft:#B7B0BF; --ink-faint:#77717F;
  --line:rgba(237,232,220,0.13); --line-strong:rgba(237,232,220,0.24);
  --brass:#D6AE58; --brass-bright:#E8C878; --brass-ink:#F0D68E;
  --shadow:rgba(0,0,0,0.55); --scrim:rgba(6,6,9,0.65);
  --good:#7FBE94; --warn:#E0A85C; --pending:#9AA0D6;
  --accent-1a:#141E45; --accent-1b:#3A4C9E; --accent-1g:#8DA0F5;
  --accent-2a:#232B10; --accent-2b:#5A4E1F; --accent-2g:#E29A44;
  --accent-3a:#341A49; --accent-3b:#6E331A; --accent-3g:#F5934A;
}

*{box-sizing:border-box;}
html{-webkit-text-size-adjust:100%;}
body{
  margin:0; background:var(--paper); color:var(--ink);
  font-family:var(--font-body); font-optical-sizing:auto;
  line-height:1.5; min-height:100vh;
}
h1,h2,h3,h4{font-family:var(--font-display); font-weight:800; letter-spacing:0.01em; text-wrap:balance; margin:0;}
a{color:inherit;}
button{font-family:inherit;}
.eyebrow{font-family:var(--font-mono); font-size:0.72rem; letter-spacing:0.14em; text-transform:uppercase; color:var(--ink-faint);}
.sr-only{position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap;}
:focus-visible{outline:2px solid var(--brass-bright); outline-offset:3px; border-radius:2px;}

/* ---------- chrome ---------- */
.chrome{
  position:sticky; top:0; z-index:40;
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  padding:0.9rem 1.6rem; background:color-mix(in srgb, var(--paper) 88%, transparent);
  backdrop-filter:blur(10px); border-bottom:1px solid var(--line);
}
.wordmark{display:flex; align-items:baseline; gap:0.55rem; text-decoration:none; cursor:pointer; background:none; border:none; padding:0; color:inherit;}
.wordmark .mark{font-family:var(--font-display); font-weight:900; font-size:1.5rem; letter-spacing:0.01em;}
.wordmark .sub{font-family:var(--font-mono); font-size:0.68rem; letter-spacing:0.16em; text-transform:uppercase; color:var(--brass);}
.chrome-right{display:flex; align-items:center; gap:1.1rem;}
.crumbs{font-family:var(--font-mono); font-size:0.78rem; color:var(--ink-soft); display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;}
.crumbs button{background:none; border:none; color:var(--brass-ink); cursor:pointer; font-family:inherit; font-size:inherit; padding:0.15rem 0.3rem; border-radius:4px;}
.crumbs button:hover{background:var(--paper-sunken);}
.crumbs .sep{color:var(--ink-faint);}
.crumbs .current{color:var(--ink);}

.role-badge{
  display:flex; align-items:center; gap:0.5rem; font-family:var(--font-mono); font-size:0.68rem;
  letter-spacing:0.06em; text-transform:uppercase; color:var(--brass-ink); border:1px solid var(--brass);
  border-radius:999px; padding:0.32rem 0.7rem 0.32rem 0.55rem;
}
.role-badge .dot{width:0.5rem; height:0.5rem; border-radius:50%; background:var(--good); box-shadow:0 0 0 3px color-mix(in srgb, var(--good) 25%, transparent); flex:none;}

main{max-width:1240px; margin:0 auto; padding:0 1.6rem 5rem;}
.view[hidden]{display:none;}

/* ---------- hub ---------- */
.hub-hero{padding:3.4rem 0 2.2rem; max-width:44rem;}
.hub-hero h1{font-size:clamp(2.4rem,5vw,3.6rem); line-height:0.98;}
.hub-hero p{color:var(--ink-soft); font-size:1.05rem; margin-top:0.9rem; max-width:38rem;}
.hub-stats{display:flex; gap:1.6rem; margin-top:1.4rem; font-family:var(--font-mono); font-size:0.78rem; color:var(--ink-faint); flex-wrap:wrap;}
.hub-stats b{color:var(--ink); font-size:1rem; font-family:var(--font-display); font-weight:800; margin-right:0.35rem;}

.exhibits{display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1.1rem; margin-bottom:2.5rem;}
.exhibit{
  position:relative; overflow:hidden; border-radius:14px; border:1px solid var(--line-strong);
  min-height:300px; display:flex; flex-direction:column; justify-content:flex-end;
  cursor:pointer; text-align:left; padding:0; background:var(--paper-raised);
  box-shadow:0 1px 2px var(--shadow); transition:transform .25s ease, box-shadow .25s ease;
}
.exhibit:hover, .exhibit:focus-visible{transform:translateY(-4px); box-shadow:0 18px 34px var(--shadow);}
.exhibit canvas{position:absolute; inset:0; width:100%; height:100%; display:block;}
.exhibit .exhibit-body{position:relative; z-index:2; padding:1.4rem 1.5rem 1.5rem; color:#F3EFE4;}
.exhibit .exhibit-eyebrow{font-family:var(--font-mono); font-size:0.7rem; letter-spacing:0.16em; text-transform:uppercase; opacity:0.82;}
.exhibit h2{font-size:2.1rem; color:#fff; margin-top:0.25rem; line-height:0.95;}
.exhibit .exhibit-meta{margin-top:0.5rem; font-size:0.85rem; opacity:0.88; font-family:var(--font-body);}
.exhibit .exhibit-count{position:absolute; top:1.2rem; right:1.3rem; z-index:2; font-family:var(--font-mono); font-size:0.72rem; letter-spacing:0.08em; color:#F3EFE4; background:rgba(0,0,0,0.28); border:1px solid rgba(255,255,255,0.25); border-radius:999px; padding:0.28rem 0.65rem;}

.hub-note{border-top:1px dashed var(--line-strong); padding-top:1.6rem; color:var(--ink-soft); font-size:0.92rem; max-width:40rem;}
.hub-note strong{color:var(--ink);}
.hub-empty{padding:4rem 1rem; text-align:center; color:var(--ink-faint); font-family:var(--font-mono); font-size:0.9rem; border:1px dashed var(--line-strong); border-radius:14px; margin-bottom:2rem;}

/* ---------- gallery ---------- */
.gallery-head{padding:2.4rem 0 1.4rem;}
.gallery-head h1{font-size:clamp(2rem,4vw,2.8rem);}
.gallery-head p{color:var(--ink-soft); margin-top:0.5rem; max-width:36rem;}

.filterbar{
  display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;
  background:var(--paper-raised); border:1px solid var(--line); border-radius:12px;
  padding:0.85rem 1rem; margin-bottom:1.6rem;
}
.filterbar .field{display:flex; flex-direction:column; gap:0.28rem;}
.filterbar label{font-family:var(--font-mono); font-size:0.66rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--ink-faint);}
.filterbar input, .filterbar select{
  font-family:var(--font-body); font-size:0.92rem; color:var(--ink); background:var(--paper);
  border:1px solid var(--line-strong); border-radius:8px; padding:0.42rem 0.65rem; min-width:11rem;
}
.filterbar input#gallerySearch{min-width:15rem;}
.filterbar .count{margin-left:auto; font-family:var(--font-mono); font-size:0.78rem; color:var(--ink-faint); align-self:center;}

.toy-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1.1rem;}
.toy-card{
  background:var(--paper-raised); border:1px solid var(--line); border-radius:12px; overflow:hidden;
  cursor:pointer; text-align:left; padding:0; display:flex; flex-direction:column;
  box-shadow:0 1px 2px var(--shadow); transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.toy-card:hover, .toy-card:focus-visible{transform:translateY(-3px); box-shadow:0 12px 24px var(--shadow); border-color:var(--line-strong);}
.toy-card .thumb-wrap{position:relative; aspect-ratio:4/5; background:var(--paper-sunken);}
.toy-card canvas, .toy-card .photo{width:100%; height:100%; display:block; object-fit:cover;}
.toy-card .status-chip{
  position:absolute; top:0.55rem; left:0.55rem; font-family:var(--font-mono); font-size:0.63rem; letter-spacing:0.06em;
  text-transform:uppercase; padding:0.22rem 0.5rem; border-radius:999px; color:#fff; background:rgba(0,0,0,0.4);
  border:1px solid rgba(255,255,255,0.3);
}
.status-arrived{background:color-mix(in srgb, var(--good) 55%, rgba(0,0,0,0.35));}
.status-wishlist, .status-ordered, .status-pre-ordered{background:color-mix(in srgb, var(--pending) 55%, rgba(0,0,0,0.35));}
.toy-card .card-body{padding:0.85rem 0.95rem 1rem;}
.toy-card h3{font-size:1.18rem; line-height:1.02;}
.toy-card .card-meta{font-family:var(--font-mono); font-size:0.68rem; color:var(--ink-faint); letter-spacing:0.03em; margin-top:0.32rem;}
.stars{display:inline-flex; gap:0.1rem; color:var(--brass-bright); font-size:0.85rem; margin-top:0.4rem;}
.stars .off{color:var(--line-strong);}
.no-rating{font-family:var(--font-mono); font-size:0.66rem; color:var(--ink-faint); margin-top:0.45rem; letter-spacing:0.04em;}
.empty-state{padding:3rem 1rem; text-align:center; color:var(--ink-faint); font-family:var(--font-mono); font-size:0.85rem; border:1px dashed var(--line-strong); border-radius:12px;}

/* ---------- detail ---------- */
.detail-grid{display:grid; grid-template-columns:minmax(0,1.05fr) minmax(0,1fr); gap:2.4rem; padding-top:2rem; align-items:start;}
@media (max-width:860px){ .detail-grid{grid-template-columns:1fr;} }

.viewer{position:sticky; top:5rem;}
.viewer-stage{cursor:zoom-in; position:relative; border-radius:14px; overflow:hidden; border:1px solid var(--line-strong); background:var(--paper-sunken); box-shadow:0 1px 3px var(--shadow);}
.viewer-stage canvas, .viewer-stage .photo{width:100%; height:auto; display:block; aspect-ratio:4/5; object-fit:contain; background:var(--paper-sunken);}
.viewer-nav{position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.38); color:#F3EFE4; border:1px solid rgba(255,255,255,0.3); width:2.4rem; height:2.4rem; border-radius:50%; cursor:pointer; font-size:1.1rem; display:flex; align-items:center; justify-content:center;}
.viewer-nav:hover{background:rgba(0,0,0,0.55);}
.viewer-nav.prev{left:0.7rem;} .viewer-nav.next{right:0.7rem;}
.viewer-caption{display:flex; justify-content:space-between; align-items:center; margin-top:0.6rem; font-family:var(--font-mono); font-size:0.7rem; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-faint);}
.thumbs{display:flex; gap:0.6rem; margin-top:0.75rem;}
.thumb-btn{width:4.4rem; aspect-ratio:4/5; border-radius:8px; overflow:hidden; border:2px solid transparent; padding:0; cursor:pointer; background:var(--paper-sunken);}
.thumb-btn.active{border-color:var(--brass);}
.thumb-btn canvas, .thumb-btn .photo{width:100%; height:100%; display:block; object-fit:cover;}

.expand-hint{
  position:absolute; bottom:0.65rem; right:0.65rem; z-index:2; font-family:var(--font-mono); font-size:0.62rem;
  letter-spacing:0.06em; text-transform:uppercase; color:#F3EFE4; background:rgba(0,0,0,0.4);
  border:1px solid rgba(255,255,255,0.3); border-radius:999px; padding:0.28rem 0.6rem; pointer-events:none;
  opacity:0; transition:opacity .15s ease;
}
.viewer-stage:hover .expand-hint, .viewer-stage:focus-visible .expand-hint{opacity:1;}

.specimen-head{display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;}
.specimen-head .eyebrow{margin-bottom:0.35rem;}
.specimen-head h1{font-size:clamp(1.9rem,3.4vw,2.6rem); line-height:1.0;}
.status-pill{font-family:var(--font-mono); font-size:0.68rem; letter-spacing:0.08em; text-transform:uppercase; padding:0.3rem 0.7rem; border-radius:999px; white-space:nowrap; border:1px solid var(--line-strong);}
.status-pill.arrived{color:var(--good); border-color:var(--good);}
.status-pill.pending{color:var(--warn); border-color:var(--warn);}

.head-actions{display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap; justify-content:flex-end;}
.btn-edit-admin{
  font-family:var(--font-mono); font-size:0.68rem; letter-spacing:0.06em; text-transform:uppercase;
  color:var(--paper); background:var(--brass-ink); border:1px solid var(--brass-ink); border-radius:999px;
  padding:0.4rem 0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; white-space:nowrap;
}
.btn-edit-admin:hover{background:var(--brass);}
.btn-edit-admin .pencil{font-size:0.85em;}

.lede{color:var(--ink-soft); margin-top:0.7rem; font-size:1.05rem; max-width:34rem;}

.rating-row{display:flex; align-items:center; gap:0.6rem; margin-top:1.1rem;}
.rating-row .stars{font-size:1.15rem; margin-top:0;}
.rating-row .label{font-family:var(--font-mono); font-size:0.7rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-faint);}

.accession{margin-top:1.6rem; border:1px solid var(--line); border-radius:12px; background:var(--paper-raised); overflow:hidden;}
.accession .accession-title{font-family:var(--font-mono); font-size:0.68rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--brass-ink); padding:0.7rem 1rem 0.5rem;}
.accession dl{display:grid; grid-template-columns:auto 1fr; gap:0.5rem 1.1rem; margin:0; padding:0 1rem 1rem;}
.accession dt{font-family:var(--font-mono); font-size:0.72rem; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.04em; white-space:nowrap;}
.accession dd{margin:0; font-size:0.92rem; font-variant-numeric:tabular-nums;}

.checklist{margin-top:1.6rem;}
.checklist h2{font-family:var(--font-mono); font-weight:600; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--brass-ink); margin-bottom:0.7rem;}
.checklist ul{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:0.4rem;}
.checklist li{display:flex; align-items:center; gap:0.6rem; font-size:0.92rem; padding:0.4rem 0.65rem; border:1px solid var(--line); border-radius:8px; background:var(--paper-raised);}
.checklist .tick{width:1.1rem; height:1.1rem; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.65rem; flex:none;}
.checklist .tick.present{background:color-mix(in srgb, var(--good) 30%, transparent); color:var(--good); border:1px solid var(--good);}
.checklist .tick.missing{background:color-mix(in srgb, var(--warn) 25%, transparent); color:var(--warn); border:1px solid var(--warn);}
.checklist .item-type{margin-left:auto; font-family:var(--font-mono); font-size:0.64rem; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.05em;}
.checklist .repro-badge{font-family:var(--font-mono); font-size:0.6rem; letter-spacing:0.05em; text-transform:uppercase; color:var(--brass-ink); border:1px solid var(--brass); border-radius:999px; padding:0.05rem 0.4rem;}
.checklist .empty{color:var(--ink-faint); font-size:0.88rem; font-style:italic;}

.notes-block{margin-top:1.6rem; font-size:0.92rem; color:var(--ink-soft); border-left:3px solid var(--brass); padding-left:0.9rem;}

.collector-notes{margin-top:1.6rem;}
.collector-notes h2{font-family:var(--font-mono); font-weight:600; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--brass-ink); margin-bottom:0.6rem;}
.collector-notes .note-body{
  font-family:var(--font-body); font-style:italic; font-size:0.98rem; color:var(--ink); line-height:1.6;
  background:var(--paper-raised); border:1px solid var(--line); border-left:3px solid var(--brass); border-radius:0 10px 10px 0;
  padding:0.9rem 1.1rem; white-space:pre-wrap;
}
.collector-notes .note-empty{font-family:var(--font-mono); font-size:0.8rem; color:var(--ink-faint); font-style:normal;}

/* ---------- lightbox ---------- */
.lightbox{position:fixed; inset:0; z-index:200; background:var(--scrim); display:flex; flex-direction:column;}
.lightbox[hidden]{display:none;}
.lightbox-close{
  position:absolute; top:1.1rem; right:1.2rem; z-index:3; width:2.6rem; height:2.6rem; border-radius:50%;
  background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.3); color:#F3EFE4; font-size:1.2rem; cursor:pointer;
}
.lightbox-close:hover{background:rgba(0,0,0,0.55);}
.lightbox-nav{
  position:absolute; top:50%; transform:translateY(-50%); z-index:3; background:rgba(0,0,0,0.35); color:#F3EFE4;
  border:1px solid rgba(255,255,255,0.3); width:3rem; height:3rem; border-radius:50%; cursor:pointer; font-size:1.4rem;
}
.lightbox-nav:hover{background:rgba(0,0,0,0.55);}
.lightbox-nav.prev{left:1.2rem;} .lightbox-nav.next{right:1.2rem;}
.lightbox-track{flex:1; display:flex; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none;}
.lightbox-track::-webkit-scrollbar{display:none;}
.lightbox-slide{flex:0 0 100%; scroll-snap-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:0.9rem; padding:3.5rem 4rem;}
.lightbox-slide canvas, .lightbox-slide .photo{height:min(78vh, 88vw * 1.25); width:auto; max-width:88vw; aspect-ratio:4/5; border-radius:10px; box-shadow:0 24px 60px rgba(0,0,0,0.5);}
.lightbox-slide .photo{object-fit:contain; background:#0b0a0d;}
.lightbox-caption{font-family:var(--font-mono); font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#F3EFE4; opacity:0.85;}
.lightbox-dots{display:flex; justify-content:center; gap:0.5rem; padding:1.1rem 0 1.4rem;}
.lightbox-dots button{width:0.5rem; height:0.5rem; border-radius:50%; border:none; background:rgba(255,255,255,0.35); cursor:pointer; padding:0;}
.lightbox-dots button.active{background:#F3EFE4;}

.versions{margin-top:2.6rem; border-top:1px solid var(--line); padding-top:1.6rem;}
.versions h2{font-family:var(--font-mono); font-weight:600; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--brass-ink);}
.versions .versions-sub{color:var(--ink-faint); font-size:0.82rem; margin-top:0.3rem; max-width:36rem;}
.version-rail{display:flex; gap:0.9rem; margin-top:1rem; overflow-x:auto; padding-bottom:0.4rem;}
.version-card{flex:none; width:9rem; cursor:pointer; border:1px solid var(--line); border-radius:10px; overflow:hidden; background:var(--paper-raised); padding:0;}
.version-card canvas, .version-card .photo{width:100%; aspect-ratio:4/5; display:block; object-fit:cover;}
.version-card .vc-body{padding:0.5rem 0.6rem 0.65rem;}
.version-card .vc-name{font-size:0.82rem; font-weight:600; line-height:1.15;}
.version-card .vc-meta{font-family:var(--font-mono); font-size:0.62rem; color:var(--ink-faint); margin-top:0.2rem;}
.version-empty{color:var(--ink-faint); font-size:0.85rem; font-style:italic; margin-top:0.9rem;}

::-webkit-scrollbar{height:8px; width:8px;}
::-webkit-scrollbar-thumb{background:var(--line-strong); border-radius:4px;}

@media (prefers-reduced-motion: reduce){ .exhibit, .toy-card{transition:none;} }
</style>
</head>
<body>

<header class="chrome">
  <button class="wordmark" id="btnHome" aria-label="Back to hub">
    <span class="mark">The Display Case</span>
    <span class="sub">My Collection</span>
  </button>
  <div class="chrome-right">
    <nav class="crumbs" id="crumbs" aria-label="Breadcrumb"></nav>
    <span class="role-badge" id="roleBadge" hidden><span class="dot"></span>Signed in &middot; Admin</span>
  </div>
</header>

<main id="app">
  <section id="view-hub" class="view"></section>
  <section id="view-gallery" class="view" hidden></section>
  <section id="view-detail" class="view" hidden></section>
</main>

<div class="lightbox" id="lightbox" hidden>
  <button class="lightbox-close" id="lightboxClose" aria-label="Close">&times;</button>
  <button class="lightbox-nav prev" id="lightboxPrev" aria-label="Previous image">&#8249;</button>
  <button class="lightbox-nav next" id="lightboxNext" aria-label="Next image">&#8250;</button>
  <div class="lightbox-track" id="lightboxTrack"></div>
  <div class="lightbox-dots" id="lightboxDots"></div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="entity-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-vw-85 modal-dialog-centered">
        <div class="modal-content" id="entity-modal-content"></div>
    </div>
</div>
<?php endif; ?>

<script>
"use strict";

/* ================= real data, embedded server-side from collection_toys ================= */
const COLLECTION = <?= $collectionJson ?>;
const UNIVERSES = <?= $universesJson ?>;
const SERVER_IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;

function getVar(name){ return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }

/* Universes are whatever the collection actually contains, in whatever order the server
   sent them — cycle a fixed accent palette across them so any number of universes gets a
   distinct look without hardcoding names. */
const ACCENT_PALETTE = [
  { c1:getVar('--accent-1a'), c2:getVar('--accent-1b'), glow:getVar('--accent-1g') },
  { c1:getVar('--accent-2a'), c2:getVar('--accent-2b'), glow:getVar('--accent-2g') },
  { c1:getVar('--accent-3a'), c2:getVar('--accent-3b'), glow:getVar('--accent-3g') },
];
const ACCENT_MAP = {};
UNIVERSES.forEach((u,i) => { ACCENT_MAP[u.slug] = ACCENT_PALETTE[i % ACCENT_PALETTE.length]; });
function accentFor(slug){ return ACCENT_MAP[slug] || ACCENT_PALETTE[0]; }

/* ================= admin state — a real server-side session/role check, not a toggle ================= */
function isAdmin(){ return SERVER_IS_ADMIN; }
function paintRoleBadge(){ document.getElementById('roleBadge').hidden = !isAdmin(); }

/* ================= canvas "specimen art" — generative stand-ins for toys with no photos yet ================= */
let grainTile = null;
function getGrain(){
  if (grainTile) return grainTile;
  const c = document.createElement('canvas'); c.width = 96; c.height = 96;
  const g = c.getContext('2d'); const img = g.createImageData(96,96);
  for (let i=0;i<img.data.length;i+=4){
    const v = 255; const a = Math.random()*22;
    img.data[i]=v; img.data[i+1]=v; img.data[i+2]=v; img.data[i+3]=a;
  }
  g.putImageData(img,0,0);
  grainTile = c;
  return grainTile;
}
function hashStr(s){ let h=0; for(let i=0;i<s.length;i++){ h = (h*31 + s.charCodeAt(i)) >>> 0; } return h; }
function mulberry32(seed){ return function(){ seed |= 0; seed = seed + 0x6D2B79F5 | 0; let t = Math.imul(seed ^ seed >>> 15, 1 | seed); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }

function drawSilhouette(ctx,w,h,type,color){
  ctx.save();
  ctx.fillStyle = color;
  ctx.globalAlpha = 0.9;
  const cx = w/2, baseY = h*0.62;
  if (type === "Vehicle"){
    ctx.beginPath();
    ctx.moveTo(cx - w*0.36, baseY + h*0.06);
    ctx.lineTo(cx - w*0.06, baseY - h*0.20);
    ctx.lineTo(cx + w*0.08, baseY - h*0.20);
    ctx.lineTo(cx + w*0.40, baseY + h*0.02);
    ctx.lineTo(cx + w*0.30, baseY + h*0.11);
    ctx.lineTo(cx - w*0.02, baseY + h*0.03);
    ctx.lineTo(cx - w*0.30, baseY + h*0.14);
    ctx.closePath(); ctx.fill();
    ctx.beginPath(); ctx.ellipse(cx-w*0.02, baseY-h*0.06, w*0.05, h*0.045, 0, 0, Math.PI*2); ctx.fillStyle="rgba(255,255,255,0.28)"; ctx.fill();
  } else if (type === "Playset"){
    ctx.fillRect(cx - w*0.34, baseY - h*0.02, w*0.68, h*0.20);
    ctx.beginPath(); ctx.arc(cx, baseY - h*0.06, w*0.13, Math.PI, 0); ctx.fill();
    ctx.fillRect(cx - w*0.30, baseY - h*0.24, w*0.11, h*0.24);
    ctx.fillRect(cx + w*0.19, baseY - h*0.24, w*0.11, h*0.24);
    ctx.globalCompositeOperation = "destination-out";
    ctx.beginPath(); ctx.arc(cx, baseY + h*0.02, w*0.055, Math.PI, 0); ctx.fill();
    ctx.globalCompositeOperation = "source-over";
  } else {
    ctx.beginPath(); ctx.arc(cx, baseY - h*0.30, w*0.075, 0, Math.PI*2); ctx.fill();
    ctx.beginPath();
    ctx.moveTo(cx - w*0.10, baseY - h*0.20);
    ctx.lineTo(cx + w*0.10, baseY - h*0.20);
    ctx.lineTo(cx + w*0.13, baseY + h*0.06);
    ctx.lineTo(cx - w*0.13, baseY + h*0.06);
    ctx.closePath(); ctx.fill();
    ctx.save(); ctx.translate(cx, baseY - h*0.15); ctx.rotate(-0.5);
    ctx.fillRect(-w*0.03, -h*0.02, w*0.19, h*0.045); ctx.restore();
    ctx.save(); ctx.translate(cx, baseY - h*0.15); ctx.rotate(0.45);
    ctx.fillRect(-w*0.16, -h*0.02, w*0.19, h*0.045); ctx.restore();
    ctx.fillRect(cx - w*0.105, baseY + h*0.05, w*0.075, h*0.20);
    ctx.fillRect(cx + w*0.03, baseY + h*0.05, w*0.075, h*0.20);
  }
  ctx.restore();
}

function drawCardback(ctx,w,h,toy){
  ctx.save();
  const items = toy.items.length ? toy.items : [{name:"No cataloged parts yet", type:""}];
  ctx.fillStyle = "rgba(255,255,255,0.10)";
  ctx.fillRect(w*0.08, h*0.10, w*0.84, h*0.78);
  ctx.strokeStyle = "rgba(255,255,255,0.30)"; ctx.lineWidth = 1.5;
  ctx.strokeRect(w*0.08, h*0.10, w*0.84, h*0.78);
  ctx.fillStyle = "#F3EFE4"; ctx.textBaseline = "middle";
  ctx.font = "600 " + Math.round(h*0.032) + "px 'IBM Plex Mono', monospace";
  ctx.fillText("SPECIMEN CARD", w*0.13, h*0.19);
  ctx.font = "400 " + Math.round(h*0.026) + "px 'IBM Plex Mono', monospace";
  ctx.globalAlpha = 0.75;
  items.slice(0,6).forEach((it,i)=>{
    const y = h*0.29 + i*h*0.085;
    ctx.strokeStyle = "rgba(255,255,255,0.55)";
    ctx.strokeRect(w*0.13, y - h*0.017, h*0.034, h*0.034);
    if (it.present !== false){
      ctx.beginPath(); ctx.moveTo(w*0.135, y); ctx.lineTo(w*0.145, y+h*0.012); ctx.lineTo(w*0.163, y-h*0.015);
      ctx.strokeStyle = "#F3EFE4"; ctx.lineWidth = 2; ctx.stroke();
    }
    ctx.fillStyle = "#F3EFE4";
    ctx.fillText(truncate(it.name, 22), w*0.19, y);
  });
  ctx.globalAlpha = 1;
  ctx.save(); ctx.translate(w*0.82, h*0.82); ctx.rotate(-0.18);
  ctx.strokeStyle = "rgba(255,255,255,0.55)"; ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.ellipse(0,0, w*0.11, h*0.055, 0, 0, Math.PI*2); ctx.stroke();
  ctx.font = "600 " + Math.round(h*0.02) + "px 'IBM Plex Mono', monospace";
  ctx.fillStyle = "rgba(255,255,255,0.75)"; ctx.textAlign = "center";
  ctx.fillText("No. " + (toy.sku || "—"), 0, 3);
  ctx.restore();
  ctx.restore();
}

function drawFlatlay(ctx,w,h,toy,rand){
  ctx.save();
  const items = toy.items.length ? toy.items : [{name:toy.name, type:toy.productType}];
  const n = items.length;
  const cols = Math.min(3, Math.max(1,n));
  const rows = Math.ceil(n/cols);
  const cellW = (w*0.78)/cols, cellH = (h*0.62)/rows;
  items.forEach((it,i)=>{
    const col = i % cols, row = Math.floor(i/cols);
    const x = w*0.11 + col*cellW + cellW/2;
    const y = h*0.20 + row*cellH + cellH/2;
    const angle = (rand() - 0.5) * 0.5;
    ctx.save(); ctx.translate(x,y); ctx.rotate(angle);
    ctx.fillStyle = "rgba(0,0,0,0.18)";
    ctx.fillRect(-cellW*0.30+3, -cellH*0.24+4, cellW*0.60, cellH*0.48);
    ctx.fillStyle = it.present === false ? "rgba(255,255,255,0.14)" : "rgba(255,255,255,0.9)";
    ctx.fillRect(-cellW*0.30, -cellH*0.24, cellW*0.60, cellH*0.48);
    ctx.strokeStyle = "rgba(0,0,0,0.25)"; ctx.strokeRect(-cellW*0.30, -cellH*0.24, cellW*0.60, cellH*0.48);
    ctx.fillStyle = it.present === false ? "rgba(255,255,255,0.55)" : "#221F27";
    ctx.font = "500 " + Math.round(h*0.021) + "px 'IBM Plex Mono', monospace";
    ctx.textAlign = "center"; ctx.textBaseline = "middle";
    wrapText(ctx, truncate(it.name,18), 0, 0, cellW*0.54, h*0.022);
    ctx.restore();
  });
  ctx.restore();
}

function wrapText(ctx,text,x,y,maxWidth,lineHeight){
  const words = text.split(" "); let line = ""; const lines = [];
  words.forEach(word=>{
    const test = line ? line+" "+word : word;
    if (ctx.measureText(test).width > maxWidth && line){ lines.push(line); line = word; } else { line = test; }
  });
  lines.push(line);
  const startY = y - (lines.length-1)*lineHeight/2;
  lines.forEach((l,i)=> ctx.fillText(l, x, startY + i*lineHeight));
}
function truncate(s,n){ return s && s.length > n ? s.slice(0,n-1)+"…" : (s||""); }

function drawNameBar(ctx,w,h,toy){
  ctx.save();
  const bh = h*0.155;
  const g = ctx.createLinearGradient(0,h-bh,0,h);
  g.addColorStop(0,"rgba(0,0,0,0)"); g.addColorStop(1,"rgba(0,0,0,0.55)");
  ctx.fillStyle = g; ctx.fillRect(0,h-bh,w,bh);
  ctx.fillStyle = "#F6F2E7"; ctx.textBaseline = "alphabetic";
  ctx.font = "800 " + Math.round(h*0.058) + "px 'Big Shoulders Display', sans-serif";
  ctx.fillText(toy.name.toUpperCase(), w*0.06, h*0.955, w*0.88);
  ctx.font = "500 " + Math.round(h*0.024) + "px 'IBM Plex Mono', monospace";
  ctx.fillStyle = "rgba(246,242,231,0.8)";
  ctx.fillText((toy.year||"") + (toy.wave? "  ·  "+toy.wave : ""), w*0.06, h*0.875);
  ctx.restore();
}

function paintSpecimen(canvas, toy, facet, opts){
  opts = opts || {};
  const dpr = Math.min(window.devicePixelRatio||1, 2);
  const rect = canvas.getBoundingClientRect();
  const cssW = rect.width || canvas.width || 320;
  const cssH = cssW * 1.25;
  canvas.width = cssW*dpr; canvas.height = cssH*dpr;
  const ctx = canvas.getContext('2d');
  ctx.setTransform(dpr,0,0,dpr,0,0);
  const w = cssW, h = cssH;
  const acc = accentFor(toy.universeSlug);
  const rand = mulberry32(hashStr(toy.slug + "-" + facet));

  const grad = ctx.createLinearGradient(0,0,w,h);
  grad.addColorStop(0, acc.c1); grad.addColorStop(1, acc.c2);
  ctx.fillStyle = grad; ctx.fillRect(0,0,w,h);

  const rg = ctx.createRadialGradient(w*0.5,h*0.36,h*0.06, w*0.5,h*0.5,h*0.85);
  rg.addColorStop(0, hexA(acc.glow, 0.35));
  rg.addColorStop(1, "rgba(0,0,0,0.40)");
  ctx.fillStyle = rg; ctx.fillRect(0,0,w,h);

  if (facet === "front") drawSilhouette(ctx,w,h,toy.productType, hexA("#F6F2E7",0.92));
  else if (facet === "cardback") drawCardback(ctx,w,h,toy);
  else drawFlatlay(ctx,w,h,toy,rand);

  const grain = getGrain();
  ctx.save(); ctx.globalAlpha = 0.5;
  const pat = ctx.createPattern(grain, 'repeat');
  ctx.fillStyle = pat; ctx.fillRect(0,0,w,h);
  ctx.restore();

  if (!opts.bare) drawNameBar(ctx,w,h,toy);
}
function hexA(hex, a){
  if (hex.startsWith('#')){
    const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${a})`;
  }
  return hex;
}
COLLECTION.forEach(t => t.slug = (t.name+"-"+t.id).toLowerCase().replace(/[^a-z0-9]+/g,'-'));

/* ================= domain helpers ================= */
const PROCEDURAL_FACETS = [
  { key:"front", label:"Front" },
  { key:"cardback", label:"Specimen card" },
  { key:"flatlay", label:"Parts flatlay" }
];
/** Real uploaded photos win when there are any; otherwise fall back to generative art. */
function getFacets(toy){
  if (toy.photos && toy.photos.length){
    return toy.photos.map((url,i) => ({ key:'photo-'+i, label: toy.photos.length>1 ? 'Photo '+(i+1) : 'Photo', url }));
  }
  return PROCEDURAL_FACETS;
}
function facetMediaHtml(toy, facet){
  if (facet.url) return `<img class="photo" src="${escapeHtml(facet.url)}" alt="${escapeHtml(toy.name)}" loading="lazy">`;
  return `<canvas></canvas>`;
}
function statusClass(status){
  return status === "Arrived" ? "arrived" : "pending";
}
function starRow(n){
  if (!n) return '<span class="no-rating">Not rated yet</span>';
  let s = '<span class="stars" aria-label="'+n+' of 5 stars">';
  for (let i=1;i<=5;i++) s += i<=n ? '★' : '<span class="off">★</span>';
  return s + '</span>';
}
function cherishSortValue(t){ return t.cherish ? t.cherish : -1; }
function otherPhysicalCopies(toy){ return COLLECTION.filter(t => t.catalogId === toy.catalogId && t.id !== toy.id); }
function characterKey(name){ return name.replace(/\s*\([^)]*\)\s*$/,'').trim().toLowerCase(); }
function characterLabel(name){ return name.replace(/\s*\([^)]*\)\s*$/,'').trim(); }
function otherLineVersions(toy){
  const key = characterKey(toy.name);
  return COLLECTION.filter(t => t.id !== toy.id && t.catalogId !== toy.catalogId && characterKey(t.name) === key);
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function formatDate(d){ const dt = new Date(d+'T00:00:00'); return dt.toLocaleDateString(undefined,{year:'numeric',month:'short',day:'numeric'}); }

/* ================= routing ================= */
function parseHash(){
  const h = location.hash.replace(/^#\/?/, '');
  const parts = h.split('/').filter(Boolean);
  if (parts[0] === 'universe' && parts[1]) return { view:'gallery', universe: parts[1] };
  if (parts[0] === 'toy' && parts[1]) return { view:'detail', id: Number(parts[1]) };
  return { view:'hub' };
}
window.addEventListener('hashchange', render);
document.getElementById('btnHome').addEventListener('click', ()=>{ location.hash = '#/'; });

function setCrumbs(items){
  const el = document.getElementById('crumbs');
  el.innerHTML = items.map((it,i)=>{
    const sep = i>0 ? '<span class="sep">/</span>' : '';
    if (it.href){ return sep + `<button data-href="${it.href}">${escapeHtml(it.label)}</button>`; }
    return sep + `<span class="current">${escapeHtml(it.label)}</span>`;
  }).join('');
  el.querySelectorAll('button[data-href]').forEach(b=>{
    b.addEventListener('click', ()=>{ location.hash = b.getAttribute('data-href'); });
  });
}

/* ================= hub view ================= */
function renderHub(){
  setCrumbs([{label:'Hub'}]);
  const el = document.getElementById('view-hub');

  if (!COLLECTION.length){
    el.innerHTML = `
      <div class="hub-hero">
        <p class="eyebrow">My collection, on display</p>
        <h1>Nothing on the shelf yet.</h1>
        <p>Add a toy to your collection from the admin and it'll show up here.</p>
      </div>`;
    return;
  }

  const total = COLLECTION.length;
  const arrived = COLLECTION.filter(t=>t.status==="Arrived").length;
  const cherished = COLLECTION.filter(t=>t.cherish>=4).length;

  el.innerHTML = `
    <div class="hub-hero">
      <p class="eyebrow">My collection, on display</p>
      <h1>${UNIVERSES.length} case${UNIVERSES.length===1?'':'s'}.<br>${total} toy${total===1?'':'s'} worth stopping for.</h1>
      <p>A walk-through of what's actually on the shelf — plus what's ordered, pre-ordered, and still on the wishlist. Pick a case to browse it properly.</p>
      <div class="hub-stats">
        <span><b>${total}</b>toy${total===1?'':'s'} tracked</span>
        <span><b>${arrived}</b>on the shelf</span>
        <span><b>${cherished}</b>rated 4★ or higher</span>
      </div>
    </div>
    <div class="exhibits">
      ${UNIVERSES.map((u,i)=>{
        const list = COLLECTION.filter(t=>t.universeSlug===u.slug);
        return `<button class="exhibit" data-slug="${escapeHtml(u.slug)}" aria-label="Browse ${escapeHtml(u.name)}">
          <canvas></canvas>
          <span class="exhibit-count">${list.length} toy${list.length===1?'':'s'}</span>
          <div class="exhibit-body">
            <div class="exhibit-eyebrow">Case ${i+1}</div>
            <h2>${escapeHtml(u.name)}</h2>
            <div class="exhibit-meta">${escapeHtml(u.blurb)}</div>
          </div>
        </button>`;
      }).join('')}
    </div>
    <p class="hub-note"><strong>About "other versions":</strong> each toy page cross-links two different things — other physical copies of the exact same catalog entry you own, and (as a preview) other toy-line releases whose name matches closely. The second one is name-matching only; the catalog doesn't yet have a real shared "character" concept linking separate entries, so treat those as a rough first pass rather than a guarantee.</p>
  `;
  el.querySelectorAll('.exhibit').forEach(btn=>{
    const slug = btn.getAttribute('data-slug');
    const sample = COLLECTION.find(t=>t.universeSlug===slug);
    if (sample) paintSpecimen(btn.querySelector('canvas'), sample, 'front', {bare:true});
    btn.addEventListener('click', ()=>{ location.hash = '#/universe/'+slug; });
  });
}

/* ================= gallery view ================= */
let galleryState = { search:'', status:'all', sort:'name' };
function renderGallery(slug){
  const universe = UNIVERSES.find(u=>u.slug===slug);
  if (!universe){ location.hash = '#/'; return; }
  setCrumbs([{label:'Hub', href:'#/'}, {label:universe.name}]);
  galleryState = { search:'', status:'all', sort:'name' };

  const el = document.getElementById('view-gallery');
  el.innerHTML = `
    <div class="gallery-head">
      <p class="eyebrow">Case ${UNIVERSES.indexOf(universe)+1} of ${UNIVERSES.length}</p>
      <h1>${escapeHtml(universe.name)}</h1>
      <p>${escapeHtml(universe.blurb)}</p>
    </div>
    <div class="filterbar">
      <div class="field">
        <label for="gallerySearch">Search</label>
        <input id="gallerySearch" type="search" placeholder="Name, SKU, wave…">
      </div>
      <div class="field">
        <label for="galleryStatus">Status</label>
        <select id="galleryStatus">
          <option value="all">All</option>
          <option value="Arrived">Arrived</option>
          <option value="Ordered">Ordered</option>
          <option value="Pre-ordered">Pre-ordered</option>
          <option value="Wishlist">Wishlist</option>
        </select>
      </div>
      <div class="field">
        <label for="gallerySort">Sort by</label>
        <select id="gallerySort">
          <option value="name">Name, A–Z</option>
          <option value="year">Year, oldest first</option>
          <option value="cherish">Cherish level</option>
        </select>
      </div>
      <span class="count" id="galleryCount"></span>
    </div>
    <div class="toy-grid" id="galleryGrid"></div>
  `;
  document.getElementById('gallerySearch').addEventListener('input', e=>{ galleryState.search = e.target.value; paintGrid(slug); });
  document.getElementById('galleryStatus').addEventListener('change', e=>{ galleryState.status = e.target.value; paintGrid(slug); });
  document.getElementById('gallerySort').addEventListener('change', e=>{ galleryState.sort = e.target.value; paintGrid(slug); });
  paintGrid(slug);
}

function paintGrid(slug){
  let list = COLLECTION.filter(t=>t.universeSlug===slug);
  const q = galleryState.search.trim().toLowerCase();
  if (q) list = list.filter(t => (t.name+' '+t.sku+' '+t.wave).toLowerCase().includes(q));
  if (galleryState.status !== 'all') list = list.filter(t => t.status === galleryState.status);
  if (galleryState.sort === 'name') list = list.slice().sort((a,b)=>a.name.localeCompare(b.name));
  else if (galleryState.sort === 'year') list = list.slice().sort((a,b)=>(a.year||0)-(b.year||0));
  else if (galleryState.sort === 'cherish') list = list.slice().sort((a,b)=> cherishSortValue(b) - cherishSortValue(a) || a.name.localeCompare(b.name));

  document.getElementById('galleryCount').textContent = list.length + ' shown';
  const grid = document.getElementById('galleryGrid');
  if (!list.length){
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">Nothing matches that search.</div>`;
    return;
  }
  grid.innerHTML = list.map(t => {
    const facet = getFacets(t)[0];
    return `
    <button class="toy-card" data-id="${t.id}" aria-label="View ${escapeHtml(t.name)}">
      <div class="thumb-wrap">
        <span class="status-chip status-${escapeHtml(t.status.toLowerCase().replace(/\s+/g,'-'))}">${escapeHtml(t.status)}</span>
        ${facetMediaHtml(t, facet)}
      </div>
      <div class="card-body">
        <h3>${escapeHtml(t.name)}</h3>
        <div class="card-meta">${t.year || '—'} · ${escapeHtml(t.toyLine)}</div>
        ${starRow(t.cherish)}
      </div>
    </button>
  `;
  }).join('');
  grid.querySelectorAll('.toy-card').forEach(card=>{
    const id = Number(card.getAttribute('data-id'));
    const toy = COLLECTION.find(t=>t.id===id);
    const facet = getFacets(toy)[0];
    const cv = card.querySelector('canvas');
    if (cv) paintSpecimen(cv, toy, facet.key);
    card.addEventListener('click', ()=>{ location.hash = '#/toy/'+id; });
  });
}

/* ================= detail view ================= */
let detailFacetIndex = 0;
function renderDetail(id){
  const toy = COLLECTION.find(t=>t.id===id);
  if (!toy){ location.hash = '#/'; return; }
  const universe = UNIVERSES.find(u=>u.slug===toy.universeSlug);
  detailFacetIndex = 0;
  setCrumbs([{label:'Hub', href:'#/'}, {label:universe ? universe.name : toy.universe, href: universe ? '#/universe/'+universe.slug : '#/'}, {label:toy.name}]);

  const physical = otherPhysicalCopies(toy);
  const lines = otherLineVersions(toy);
  const el = document.getElementById('view-detail');

  el.innerHTML = `
    <div class="detail-grid">
      <div class="viewer">
        <div class="viewer-stage" id="viewerStage" tabindex="0" role="button" aria-label="View ${escapeHtml(toy.name)} full size">
          <div id="mainStageWrap"></div>
          <button class="viewer-nav prev" id="stagePrev" aria-label="Previous image">&#8249;</button>
          <button class="viewer-nav next" id="stageNext" aria-label="Next image">&#8250;</button>
          <span class="expand-hint">Click to enlarge</span>
        </div>
        <div class="viewer-caption"><span id="facetLabel"></span><span>${escapeHtml(toy.name)}</span></div>
        <div class="thumbs" id="thumbRow"></div>
      </div>
      <div class="specimen">
        <div class="specimen-head">
          <div>
            <div class="eyebrow">${escapeHtml(toy.toyLine)}</div>
            <h1>${escapeHtml(toy.name)}</h1>
          </div>
          <div class="head-actions">
            <span class="status-pill ${statusClass(toy.status)}">${escapeHtml(toy.status)}</span>
            ${isAdmin() ? `<button class="btn-edit-admin" id="btnEditAdmin"><span class="pencil">&#9998;</span> Edit in Admin</button>` : ''}
          </div>
        </div>
        <p class="lede">${escapeHtml(toy.description || '')}</p>
        <div class="rating-row">
          <span class="label">Cherish level</span>
          ${starRow(toy.cherish)}
        </div>
        <div class="accession">
          <div class="accession-title">Accession card</div>
          <dl>
            <dt>Universe</dt><dd>${escapeHtml(toy.universe)}</dd>
            <dt>Manufacturer</dt><dd>${escapeHtml(toy.manufacturer)}</dd>
            <dt>Toy line</dt><dd>${escapeHtml(toy.toyLine)}</dd>
            <dt>Type</dt><dd>${escapeHtml(toy.productType)}</dd>
            <dt>Released</dt><dd>${toy.year || '—'}${toy.wave ? ' · ' + escapeHtml(toy.wave) : ''}</dd>
            <dt>Catalog no.</dt><dd>${escapeHtml(toy.sku || '—')}</dd>
            <dt>Condition</dt><dd>${escapeHtml(toy.conditionGrade || '—')}</dd>
            <dt>Acquired</dt><dd>${toy.dateAcquired ? formatDate(toy.dateAcquired) : '—'}</dd>
            <dt>Source</dt><dd>${escapeHtml(toy.source || '—')}</dd>
            <dt>Stored at</dt><dd>${escapeHtml(toy.storageUnit || '—')}</dd>
          </dl>
        </div>
        <div class="checklist">
          <h2>Parts &amp; accessories</h2>
          <ul>
            ${toy.items.length ? toy.items.map(it => `
              <li>
                <span class="tick ${it.present===false?'missing':'present'}">${it.present===false?'!':'✓'}</span>
                <span>${escapeHtml(it.name)}</span>
                ${it.repro ? '<span class="repro-badge">Repro</span>' : ''}
                <span class="item-type">${escapeHtml(it.type||'')}</span>
              </li>`).join('') : '<li class="empty">No parts catalog on file for this toy yet.</li>'}
          </ul>
        </div>
        ${toy.status !== 'Arrived' ? `<p class="notes-block">Status: <strong>${escapeHtml(toy.status)}</strong> — this one isn't on the shelf yet, so the parts list above reflects the catalog record, not a physical check.</p>` : ''}

        <div class="collector-notes">
          <h2>Collector's notes</h2>
          ${toy.notes
            ? `<div class="note-body">${escapeHtml(toy.notes)}</div>`
            : (isAdmin()
                ? `<div class="note-body note-empty">No notes on this one yet — add some from Edit in Admin above.</div>`
                : `<div class="note-body note-empty">No notes on this one yet.</div>`)}
        </div>
      </div>
    </div>

    <div class="versions">
      <h2>Other copies of this exact toy</h2>
      <p class="versions-sub">Same catalog entry, different physical item — for when you own more than one.</p>
      ${physical.length ? `<div class="version-rail">${physical.map(versionCard).join('')}</div>` : '<p class="version-empty">You\'ve only got one of these right now.</p>'}
    </div>

    <div class="versions">
      <h2>Same character, other toy lines</h2>
      <p class="versions-sub">Matched by name across different releases — a preview feature, see the note on the hub page about how it works.</p>
      ${lines.length ? `<div class="version-rail">${lines.map(versionCard).join('')}</div>` : '<p class="version-empty">No other release of “' + escapeHtml(characterLabel(toy.name)) + '” in the collection yet.</p>'}
    </div>
  `;

  paintDetailStage(toy);
  document.getElementById('stagePrev').addEventListener('click', (e)=>{ e.stopPropagation(); cycleFacet(toy,-1); });
  document.getElementById('stageNext').addEventListener('click', (e)=>{ e.stopPropagation(); cycleFacet(toy,1); });
  const stage = document.getElementById('viewerStage');
  const openFromStage = ()=> openLightbox(toy, detailFacetIndex);
  stage.addEventListener('click', openFromStage);
  stage.addEventListener('keydown', (e)=>{ if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); openFromStage(); } });
  el.querySelectorAll('.version-card').forEach(card=>{
    card.addEventListener('click', ()=>{ location.hash = '#/toy/'+card.getAttribute('data-id'); });
  });
  const editBtn = document.getElementById('btnEditAdmin');
  if (editBtn) editBtn.addEventListener('click', ()=>{
    if (typeof CollectionWizard !== 'undefined') CollectionWizard.editToy(toy.id);
  });
  window.onkeydown = (e)=>{
    if (parseHash().view !== 'detail') return;
    if (!document.getElementById('lightbox').hidden) return;
    if (e.key === 'ArrowLeft') cycleFacet(toy,-1);
    if (e.key === 'ArrowRight') cycleFacet(toy,1);
  };
}

function versionCard(t){
  const facet = getFacets(t)[0];
  return `<button class="version-card" data-id="${t.id}">
    ${facetMediaHtml(t, facet)}
    <div class="vc-body">
      <div class="vc-name">${escapeHtml(t.name)}</div>
      <div class="vc-meta">${escapeHtml(t.toyLine)} · ${t.year||'—'}</div>
    </div>
  </button>`;
}

function paintDetailStage(toy){
  const facets = getFacets(toy);
  if (detailFacetIndex >= facets.length) detailFacetIndex = 0;
  const facet = facets[detailFacetIndex];

  const wrap = document.getElementById('mainStageWrap');
  wrap.innerHTML = facetMediaHtml(toy, facet);
  const mainCanvas = wrap.querySelector('canvas');
  if (mainCanvas) paintSpecimen(mainCanvas, toy, facet.key);

  document.getElementById('facetLabel').textContent = facet.label;
  const row = document.getElementById('thumbRow');
  row.innerHTML = facets.map((f,i)=>`<button class="thumb-btn ${i===detailFacetIndex?'active':''}" data-i="${i}" aria-label="${escapeHtml(f.label)}">${facetMediaHtml(toy,f)}</button>`).join('');
  row.querySelectorAll('.thumb-btn').forEach((btn,i)=>{
    const cv = btn.querySelector('canvas');
    if (cv) paintSpecimen(cv, toy, facets[i].key);
    btn.addEventListener('click', ()=>{ detailFacetIndex = i; paintDetailStage(toy); });
  });
  document.querySelectorAll('.versions .version-card').forEach(card=>{
    const id = Number(card.getAttribute('data-id'));
    const t = COLLECTION.find(x=>x.id===id);
    if (!t) return;
    const cv = card.querySelector('canvas');
    if (cv) paintSpecimen(cv, t, getFacets(t)[0].key);
  });
}
function cycleFacet(toy,dir){
  const facets = getFacets(toy);
  detailFacetIndex = (detailFacetIndex + dir + facets.length) % facets.length;
  paintDetailStage(toy);
}

/* ================= lightbox — maximized, scrollable image view ================= */
let lightboxToy = null;
function openLightbox(toy, startIndex){
  lightboxToy = toy;
  const facets = getFacets(toy);
  const track = document.getElementById('lightboxTrack');
  track.innerHTML = facets.map(f => `<div class="lightbox-slide">${facetMediaHtml(toy,f)}<div class="lightbox-caption">${escapeHtml(f.label)}</div></div>`).join('');
  track.querySelectorAll('canvas').forEach((cv,i)=> paintSpecimen(cv, toy, facets[i].key));
  const dots = document.getElementById('lightboxDots');
  dots.innerHTML = facets.map((f,i)=> `<button data-i="${i}" aria-label="Go to ${escapeHtml(f.label)}"></button>`).join('');
  dots.querySelectorAll('button').forEach(b=>{
    b.addEventListener('click', ()=> scrollLightboxTo(Number(b.getAttribute('data-i'))));
  });
  document.getElementById('lightbox').hidden = false;
  document.body.style.overflow = 'hidden';
  requestAnimationFrame(()=> scrollLightboxTo(startIndex, 'instant'));
  track.onscroll = debounce(()=> updateLightboxDots(), 100);
}
function scrollLightboxTo(i, behavior){
  const track = document.getElementById('lightboxTrack');
  track.scrollTo({ left: i * track.clientWidth, behavior: behavior || 'smooth' });
}
function updateLightboxDots(){
  const track = document.getElementById('lightboxTrack');
  const i = Math.round(track.scrollLeft / Math.max(1,track.clientWidth));
  document.querySelectorAll('.lightbox-dots button').forEach((b,idx)=> b.classList.toggle('active', idx===i));
}
function closeLightbox(){
  document.getElementById('lightbox').hidden = true;
  document.body.style.overflow = '';
  lightboxToy = null;
}
function debounce(fn, ms){ let t; return function(...a){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,a), ms); }; }

document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
document.getElementById('lightbox').addEventListener('click', (e)=>{ if (e.target.id === 'lightbox') closeLightbox(); });
document.getElementById('lightboxPrev').addEventListener('click', ()=>{
  const track = document.getElementById('lightboxTrack');
  const i = Math.max(0, Math.round(track.scrollLeft/track.clientWidth) - 1);
  scrollLightboxTo(i);
});
document.getElementById('lightboxNext').addEventListener('click', ()=>{
  const track = document.getElementById('lightboxTrack');
  const max = (lightboxToy ? getFacets(lightboxToy).length : 1) - 1;
  const i = Math.min(max, Math.round(track.scrollLeft/track.clientWidth) + 1);
  scrollLightboxTo(i);
});
window.addEventListener('keydown', (e)=>{
  if (document.getElementById('lightbox').hidden) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowLeft') document.getElementById('lightboxPrev').click();
  if (e.key === 'ArrowRight') document.getElementById('lightboxNext').click();
});

/* ================= render loop ================= */
function render(){
  const route = parseHash();
  document.getElementById('view-hub').hidden = route.view !== 'hub';
  document.getElementById('view-gallery').hidden = route.view !== 'gallery';
  document.getElementById('view-detail').hidden = route.view !== 'detail';
  window.scrollTo(0,0);
  if (route.view === 'hub') renderHub();
  else if (route.view === 'gallery') renderGallery(route.universe);
  else if (route.view === 'detail') renderDetail(route.id);
}
let resizeTimer;
window.addEventListener('resize', ()=>{
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(render, 180);
});
document.addEventListener('DOMContentLoaded', () => {
  document.fonts.ready.then(render);
  setTimeout(render, 250);
});
paintRoleBadge();
render();
</script>

<?php if ($isAdmin): ?>
<script>const SITE_URL = "<?= $e($baseUrl) ?>";</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="<?= $e($baseUrl) ?>assets/js/modules/collection/collection_toys.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof CollectionWizard === 'undefined') return;
  CollectionWizard.init();
  // The wizard has no "saved" callback we can hook here — a reload always shows the true,
  // current state, whether the visit closed the modal by saving or by cancelling.
  document.getElementById('entity-modal').addEventListener('hidden.bs.modal', () => location.reload());
});
</script>
<?php endif; ?>
</body>
</html>
