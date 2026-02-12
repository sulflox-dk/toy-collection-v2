Collection:

Packaging Status (Enum): Er den i æske eller ej?

'Loose' (Løs, pakket ud)

'MOC' (Mint on Card - Stadig på papkortet)

'MIB' (Mint in Box - Stadig i æsken, men evt. åbnet)

'MISB' (Mint in Sealed Box - Fabriksforseglet)

Personal Grading (Den klassiske skala) Vi kan bruge den internationalt anerkendte "C-scale" (Condition Scale) eller en mere læsbar standard. En ENUM kunne se sådan her ud:

'Mint (M)' (Helt perfekt, som ny)

'Near Mint (NM)' (Næsten perfekt, mikroskopisk slid)

'Excellent (EX)' (Flot stand, let slid/misfarvning)

'Very Good (VG)' (Tydeligt leget med, men komplet og pæn)

'Good (G)' (Større slid, løse led, maling mangler)

'Fair (F)' (Meget slidt, kan mangle en finger osv.)

'Poor (P)' (Ødelagt, knækket, bruges mest til reservedele)

Professional Grading Selskaber som AFA (Action Figure Authority) og UKG bruger typisk en pointskala fra 10-100. En ENUM med alle tal fra 10 til 100 er for klodset, så vi kan bygge de "kasser", du efterspørger:

Vi laver et felt til selskabet: pro_grader ENUM: 'None', 'AFA', 'UKG', 'CAS'

Vi laver et felt til kassen: pro_grade_tier ENUM:

'Gold / 90-100' (Flawless)

'Silver / 80-85' (High grade)

'Bronze / 70-75' (Mid grade)

'Standard / 10-60' (Low grade)

'Uncirculated (U)'

'Qualified (Q)'