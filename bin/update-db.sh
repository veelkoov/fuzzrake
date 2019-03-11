#!/usr/bin/env bash

pushd "$(dirname "$0")/../ansible"

ansible-playbook update-db.yaml "${@:1}"
