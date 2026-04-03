#!/usr/bin/env bash
set -euo pipefail
DOMAIN="${1:-crm_leads_customers}"
BASE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
case "$DOMAIN" in
  crm_leads_customers) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/27_CRM_LEADS_CLIENTS_FILES.txt" ;;
  sites_service jobs_time) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/28_PROJECTS_TASKS_TIME_FILES.txt" ;;
  finance_sales) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/29_FINANCE_SALES_FILES.txt" ;;
  hr_attendance_leave) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/30_HR_ATTENDANCE_LEAVE_FILES.txt" ;;
  support_comms) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/31_SUPPORT_COMMS_FILES.txt" ;;
  platform_misc) MANIFEST="$BASE_DIR/MAGICAI_PREMERGE/32_PLATFORM_MISC_FILES.txt" ;;
  *) echo "Unknown domain: $DOMAIN"; exit 1 ;;
esac
OUT_DIR="$BASE_DIR/domain_slice_$DOMAIN"
rm -rf "$OUT_DIR"
mkdir -p "$OUT_DIR"
while IFS= read -r rel; do
  [ -z "$rel" ] && continue
  mkdir -p "$OUT_DIR/$(dirname "$rel")"
  cp -a "$BASE_DIR/$rel" "$OUT_DIR/$rel"
done < "$MANIFEST"
echo "Created $OUT_DIR from $MANIFEST"
