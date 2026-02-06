# Calculating "resolved does"

Examples below are based on the following species tree:

- Most species
    - Canines
        - Dogs
        - Wolves
    - Felines
        - Lions
    - Deer

1.  Calculate list of species with depth:

        - Most species (0)
        - Canines (1)
        - Felines (1)
        - Deer (1)
        - Dogs (2)
        - Wolves (2)
        - Lions (2)

    If a specie would have two depths (e.g. "Deer" belonging to "Mammals" and "With antlers"), the larger number is used for calculations:

        - Most species (0)
          - Real life animals (1)
            - Mammals (2)
              - Deer (x)
          - With antlers (1)
            - Deer (x)

    Deer specie depth here (`x`) would be considered 3 and not 2.

2.  If a creator has _does_ list empty and the _doesn't_ list not empty, then in all calculations the _does_ list is populated with the "Most species" entry.

    Given:

        { does: [], doesn't: [Canines] }

    Get:

        { does: [Most species], doesn't: [Canines] }

3.  Combine _does_ and _doesn't_ lists with depths and `+` = does, `-` = doesn't

    Given the example creator:

        { does: [Most species, Lions], doesn't: [Felines, Dogs] }

    Get:

        +Most species (0)
        +Lions (2)
        -Felines (1)
        -Dogs (2)

    Sorted (shallowest first, `+` before `-`):

        +Most species (0)
        -Felines (1)
        +Lions (2)
        -Dogs (2)

4.  Calculate, step by step

    Given the example creator:

        { does: [Most species, Lions], doesn't: [Felines, Dogs] }

    "in" = (remaining) input data, "RD" = "resolved does".
    1.  Initial state

            in = [+Most species (0), -Felines (1), +Lions (2), -Dogs (2)]
            RD = []

    2.  Added "Most species" and all descendants:

            in = [-Felines (1), +Lions (2), -Dogs (2)]
            RD = [Most species, Canines, Dogs, Wolves, Felines, Lions, Deer]

    3.  Removed "Felines" and all descendants:

            in = [+Lions (2), -Dogs (2)]
            RD = [Most species, Canines, Dogs, Wolves, Deer]

    4.  Added "Lions" and all descendants (none in the example):

            in = [-Dogs (2)]
            RD = [Most species, Canines, Dogs, Wolves, Lions, Deer]

    5.  Removed "Dogs" and all descendants (none in the example):

            in = []
            RD = [Most species, Canines, Wolves, Lions, Deer]

5.  The result

    Given the example creator:

        { does: [Most species, Lions], doesn't: [Felines, Dogs] }

    We get:

        { does: [Most species, Lions], doesn't: [Felines, Dogs],
          resolved does: [Most species, Canines, Wolves, Lions, Deer] }

    On the example species tree:
    - Most species
        - Canines
            - ~~Dogs~~
            - Wolves
        - ~~Felines~~
            - Lions
        - Deer
