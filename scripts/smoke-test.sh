#!/bin/bash
set -euo pipefail

BASE_URL="${1:-https://opspilot.whizzweb.net}"
FAILED=0

check() {
    local url="$1"
    local label="$2"
    local status
    status=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null || echo "000")
    case "$status" in
        200) echo "[PASS] $label ($status)" ;;
        401) echo "[PASS] $label ($status - unauthenticated, expected)" ;;
        302) echo "[PASS] $label ($status - redirect, expected)" ;;
        *)   echo "[FAIL] $label ($status)"; FAILED=1 ;;
    esac
}

echo "========================================"
echo "  Smoke Test - OpsPilot Portal"
echo "  Target: $BASE_URL"
echo "========================================"
echo ""

check "$BASE_URL/api/health" "Health check"
check "$BASE_URL/login" "Login page"
check "$BASE_URL/api/documentation" "Swagger docs"

echo ""
if [ "$FAILED" -ne 0 ]; then
    echo "[FAIL] Some checks failed."
    exit 1
else
    echo "[PASS] All smoke tests passed."
    exit 0
fi
