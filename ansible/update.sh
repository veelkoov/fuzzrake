#!/usr/bin/env bash

pushd "$(dirname "$0")"

ansible-playbook update.yaml "${@:1}"
