#!/usr/bin/env python3

import re
import sys

if len(sys.argv) != 4:
    print('Expected exactly 3 arguments: input, output, output_private', file=sys.stderr)
    exit(1)

input_path: str = sys.argv[1]
output_path: str = sys.argv[2]
output_private_path: str = sys.argv[3]


def reformat_line(line: str) -> str:
    res_line = ''

    apos_open = False
    add_space = False

    for i in range(0, len(line)):
        if line[i] == "'":
            apos_open = not apos_open

        if not apos_open and line[i] == '(' and i > 0 and line[i - 1] != ' ':
            res_line += ' '

        if add_space and line[i] != ' ':
            res_line += ' '

        res_line += line[i]

        add_space = not apos_open and line[i] == ','

    return res_line


def is_private(line: str) -> bool:
    return line.find('artisans_private_data') != -1


def is_skippable(line: str) -> bool:
    return line.find('INSERT INTO artisans_commissions_statues VALUES') != -1


def remove_dynamic_data(line: str) -> str:  # TODO: Same story again. Move this data to another table?
    return re.sub(
        r"(INSERT INTO artisans_urls VALUES \(\d+, \d+, '[A-Z_]+', '.+'), (?:NULL|'[0-9: .-]+'), (?:NULL|'[0-9: .-]+'), \d+, '.*'\);\n",
        "\\1, NULL, NULL, 0 , '');\n", line)


def reformat_file() -> None:
    with open(input_path) as input_file, \
            open(output_path, 'w') as output_file, \
            open(output_private_path, 'w') as output_private_file:
        for line in input_file:
            res_line = reformat_line(line)
            res_line = remove_dynamic_data(res_line)

            if is_skippable(res_line):
                continue
            elif is_private(res_line):
                output_private_file.write(res_line)
            else:
                output_file.write(res_line)


reformat_file()
