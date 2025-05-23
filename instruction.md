| NAME    | CODE     | ARG\_1 | ARG\_2 | ARG\_3 | MATH       |
| ------- | -------- | ------ | ------ | ------ | ---------- |
| NOP     | 00000000 | /      | /      | /      | /          |
| ADD     | 00000001 | a      | b      | c      | c = b + a  |
| SUB     | 00000010 | a      | b      | c      | c = b - a  |
| LOAD    | 11000101 | imm    | imm    | a      | a = \*addr |
| HALT    | 00000111 | /      | /      | /      | /          |
| JMP     | 01001000 | imm    | /      | /      | /          |
| JEQZ    | 01001001 | imm    | b      | c      | /          |
| JEQ     | 11001010 | imm    | imm    | c      | /          |
| INCHAR  | 00001100 | /      | /      | /      | /          |
| OUTCHAR | 00001101 | /      | /      | /      | /          |
| LOADM   | 10001110 | a      | imm    | /      | \*mem = a  |
| STOREM  | 01001111 | imm    | b      | /      | \*mem = b  |

Legend:
`ARG_1` = first operand
`ARG_2` = second operand
`ARG_3` = destination or result
`imm` = immediate value (determined by bits 6 or 7)
`*addr` and `*mem` imply memory access (for load/store)
