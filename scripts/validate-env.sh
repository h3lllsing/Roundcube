#!/bin/sh
set -e

errors=0

assert_env() {
    local key="$1"
    local expected="$2"
    local actual
    actual=$(grep "^${key}=" .env 2>/dev/null | cut -d= -f2- | tr -d '"'"'" | tr -d "'")
    if [ "$actual" != "$expected" ]; then
        echo "ERROR: ${key} must be '${expected}' (found: '${actual}')"
        errors=$((errors + 1))
    fi
}

assert_env_prefix() {
    local key="$1"
    local prefix="$2"
    local actual
    actual=$(grep "^${key}=" .env 2>/dev/null | cut -d= -f2- | tr -d '"'"'" | tr -d "'")
    case "$actual" in
        ${prefix}*) ;;
        "")
            echo "ERROR: ${key} is not set"
            errors=$((errors + 1))
            ;;
        *)
            echo "ERROR: ${key} must start with '${prefix}' (found: '${actual}')"
            errors=$((errors + 1))
            ;;
    esac
}

assert_env "APP_ENV" "production"
assert_env "APP_DEBUG" "false"
assert_env_prefix "APP_URL" "https://"

if [ "$errors" -gt 0 ]; then
    echo ""
    echo "PRE-DEPLOY FAILED: ${errors} environment configuration error(s) found. Fix before deploying."
    exit 1
fi

echo ".env validation passed."
