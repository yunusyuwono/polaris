import re
import pathlib
import json
import openpyxl

wb_path = pathlib.Path(r'D:\laragon\www\digami\SARMUT KPI DAN RENSTRA 2026.xlsx')
out_dir = pathlib.Path(r'D:\laragon\www\digami\output')
out_dir.mkdir(parents=True, exist_ok=True)
out_path = out_dir / 'sarmut_pusatunitbagian.json'

wb = openpyxl.load_workbook(wb_path, data_only=True)
sheet_name = 'SARMUT PUSATUNITBAGIAN'
if sheet_name not in wb.sheetnames:
    raise SystemExit(f"Sheet {sheet_name} not found in workbook")
ws = wb[sheet_name]

# find header row by searching for 'SASARAN' keyword
header_row = None
for i in range(1, min(20, ws.max_row) + 1):
    row_vals = [str(cell.value).strip() if cell.value is not None else '' for cell in ws[i]]
    joined = ' '.join(row_vals).upper()
    if 'SASARAN' in joined and 'INDIKATOR' in joined:
        header_row = i
        header = row_vals
        break
if header_row is None:
    # fallback to previously observed row 10
    header_row = 10
    header = [str(cell.value).strip() if cell.value is not None else '' for cell in ws[header_row]]

# find title/year above header
title = None
year = None
for i in range(1, header_row):
    txt = ' '.join([str(c.value) for c in ws[i] if c.value is not None])
    if not txt:
        continue
    if 'BERDASARKAN' in txt.upper() or 'TAHUN' in txt.upper():
        title = txt.strip()
        m = re.search(r"(20\d{2})", txt)
        if m:
            year = int(m.group(1))
        break
if title is None:
    title = wb_path.stem
if year is None:
    # try file name
    m = re.search(r"(20\d{2})", wb_path.stem)
    year = int(m.group(1)) if m else None

# iterate data rows
result = {
    'title': title,
    'year': year,
    'sheet': sheet_name,
    'sasaran': []
}

sasaran_id = 0
indikator_id = 0
subindikator_id = 0
current_sasaran = None
current_indikator = None

for row in ws.iter_rows(min_row=header_row+1, max_row=ws.max_row, values_only=True):
    # normalize
    cells = [c if c is not None else '' for c in row]
    c_sasaran = str(cells[0]).strip()
    c_indikator = str(cells[1]).strip()
    # columns observed: index 5 = NO, 6 = SUB NDIKATOR, 7 = SATUAN, 8 = TARGET INSTITUSI (2026), 10 = PIC
    c_no = cells[5]
    c_sub = str(cells[6]).strip()
    c_satuan = str(cells[7]).strip()
    c_target = str(cells[8]).strip()
    c_pic = str(cells[10]).strip()

    # skip empty rows
    if not any([c_sasaran, c_indikator, c_sub, c_target, c_pic, c_no]):
        continue

    if c_sasaran and c_sasaran.upper() not in ('SASARAN STRATEGIS', 'SARMUT KPI') and 'BERDASARKAN' not in c_sasaran.upper():
        # new sasaran
        sasaran_id += 1
        current_sasaran = {
            'sasaran_id': sasaran_id,
            'sasaran': c_sasaran,
            'jenis': None,
            'indikator': []
        }
        result['sasaran'].append(current_sasaran)
        current_indikator = None
        continue

    if c_indikator and c_indikator.upper() not in ('INDIKATOR KINERJA',):
        # new indikator under current sasaran
        if current_sasaran is None:
            # create placeholder sasaran
            sasaran_id += 1
            current_sasaran = {
                'sasaran_id': sasaran_id,
                'sasaran': 'Umum',
                'jenis': None,
                'indikator': []
            }
            result['sasaran'].append(current_sasaran)
        indikator_id += 1
        current_indikator = {
            'indikator_id': indikator_id,
            'indikator': c_indikator,
            'kode': None,
            'order_no': int(c_no) if (isinstance(c_no, (int, float)) and c_no==c_no) else None,
            'subindikator': []
        }
        current_sasaran['indikator'].append(current_indikator)
        # continue; there may also be subindikator on same row

    # if there's a subindikator or indicator but no explicit sub, create sub entry
    if c_sub or (not c_sub and c_indikator and current_indikator is None):
        # ensure indicator exists
        if current_indikator is None and c_indikator:
            indikator_id += 1
            current_indikator = {
                'indikator_id': indikator_id,
                'indikator': c_indikator,
                'kode': None,
                'order_no': int(c_no) if (isinstance(c_no, (int, float)) and c_no==c_no) else None,
                'subindikator': []
            }
            if current_sasaran is None:
                sasaran_id += 1
                current_sasaran = {
                    'sasaran_id': sasaran_id,
                    'sasaran': 'Umum',
                    'jenis': None,
                    'indikator': [current_indikator]
                }
                result['sasaran'].append(current_sasaran)
            else:
                current_sasaran['indikator'].append(current_indikator)

        # create subindikator record
        if c_sub:
            subindikator_id += 1
            pics = []
            if c_pic:
                # parse PIC string e.g. ADUM+KEUANGAN+BMN
                parts = re.split(r'[+,;|]', c_pic)
                pics = [p.strip() for p in parts if p and p.strip()]
            sub = {
                'subindikator_id': subindikator_id,
                'subindikator': c_sub,
                'jenis': None,
                'satuan': c_satuan or None,
                'target': c_target or None,
                'pics': pics
            }
            if current_indikator is None:
                # create a placeholder indicator
                indikator_id += 1
                current_indikator = {
                    'indikator_id': indikator_id,
                    'indikator': c_indikator or 'Umum',
                    'kode': None,
                    'order_no': int(c_no) if (isinstance(c_no, (int, float)) and c_no==c_no) else None,
                    'subindikator': []
                }
                if current_sasaran is None:
                    sasaran_id += 1
                    current_sasaran = {
                        'sasaran_id': sasaran_id,
                        'sasaran': 'Umum',
                        'jenis': None,
                        'indikator': [current_indikator]
                    }
                    result['sasaran'].append(current_sasaran)
                else:
                    current_sasaran['indikator'].append(current_indikator)
            current_indikator['subindikator'].append(sub)
        else:
            # no sub text but maybe indicator row holds info (target/pic)
            if current_indikator is not None:
                # attach target/pic if available as a sub-like entry
                if c_target or c_pic:
                    subindikator_id += 1
                    pics = []
                    if c_pic:
                        parts = re.split(r'[+,;|]', c_pic)
                        pics = [p.strip() for p in parts if p and p.strip()]
                    sub = {
                        'subindikator_id': subindikator_id,
                        'subindikator': current_indikator.get('indikator'),
                        'jenis': None,
                        'satuan': c_satuan or None,
                        'target': c_target or None,
                        'pics': pics
                    }
                    current_indikator['subindikator'].append(sub)

# write out
with open(out_path, 'w', encoding='utf8') as f:
    json.dump(result, f, ensure_ascii=False, indent=2)

print('Exported to', out_path)
