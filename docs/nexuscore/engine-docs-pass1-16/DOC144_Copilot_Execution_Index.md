# Copilot Execution Index

## Execution Order
1. Build extension extraction matrix
2. Lock ambiguous ownership items
3. Run Bundle A classification against matrix
4. Run Bundle B naming against locked matrix
5. Run Bundle C execution wiring against target map
6. Run Bundle D governance hooks against target map
7. Run Bundle E docs/drift audit against transformed tree

## Required Artifacts Per Execution Run
- updated extraction matrix
- changed path list
- changed controller list
- changed route list
- changed table list
- unresolved ambiguity list
- rollback checkpoint reference

## Completion Rule
A Copilot run is not complete unless it updates the extraction matrix.
