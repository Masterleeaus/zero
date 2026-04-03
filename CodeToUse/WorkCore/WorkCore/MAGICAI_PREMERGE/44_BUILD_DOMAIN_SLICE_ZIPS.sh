#!/usr/bin/env bash
set -euo pipefail
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="$BASE_DIR/domain_slices"
mkdir -p "$OUT_DIR"
python - <<'PY'
import os, zipfile
base=os.environ.get('BASE_DIR')
out=os.path.join(base,'domain_slices')
manifests=['27_CRM_LEADS_CLIENTS_FILES.txt','28_PROJECTS_TASKS_TIME_FILES.txt','29_FINANCE_SALES_FILES.txt','30_HR_ATTENDANCE_LEAVE_FILES.txt','31_SUPPORT_COMMS_FILES.txt','32_PLATFORM_MISC_FILES.txt']
for mf in manifests:
    src_manifest=os.path.join(base,'MAGICAI_PREMERGE',mf)
    name=mf.split('_',1)[1].replace('.txt','').lower()
    out_zip=os.path.join(out,name+'.zip')
    with zipfile.ZipFile(out_zip,'w',zipfile.ZIP_DEFLATED) as z:
        for line in open(src_manifest):
            rel=line.strip()
            if rel:
                src=os.path.join(base, rel)
                if os.path.isfile(src):
                    z.write(src, rel)
    print(f'built {out_zip}')
PY
