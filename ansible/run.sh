#!/usr/bin/env bash

set -euo pipefail

pushd "$(dirname "$0")"

ansible-playbook "$(basename "${@:1:1}")" "${@:2}"
