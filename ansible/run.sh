#!/usr/bin/env bash

pushd "$(dirname "$0")"

ansible-playbook "$(basename "${@:1:1}")" "${@:2}"
