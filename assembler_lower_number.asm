start_array:		mov x 200
					mov 195 x
end_array:	 		mov x 209
					mov 196 x
init_x:				mov x 0
					mov 197 x
init_y:				mov x 0
					mov 198 x
init_array: 		mov x 23
					mov 200 x
					mov x 12
					mov 201 x
					mov x 80
					mov 202 x
					mov x 50
					mov 203 x
					mov x 70
					mov 204 x
					mov x 99
					mov 205 x
					mov x 205
					mov 206 x
					mov x 17
					mov 207 x
					mov x 1
					mov 208 x
					mov x 148
					mov 209 x
					mov x 195m
init_inferior:		mov y [x]
					mov 199 y
chk_assign_bucle:	mov	x 195m
					mov y 196m
chk_init:			dec x
chk_array_x:		jz x chk_array_y
					dec y
					jmp chk_init
chk_array_y:		dec y
					jz y done
					jmp get_pointer
get_pointer:		mov x 195m
inc_acum_x:			inc x
set_acum_x:			mov 195 x
set_reg_y:			mov y [x]
save_reg_y:			mov 198 y
set_reg_x:			mov x 199m
save_reg_x:			mov 197 x
dec_reg_x:			dec x
dec_reg_y:			dec y
					jz x reg_x_inf
					jz y reg_y_inf
bucle_return:		jmp dec_reg_x
reg_x_inf:			mov x 197m
					mov 199 x
					jmp chk_assign_bucle
reg_y_inf:			mov y 198m
					mov 199 y
					jmp chk_assign_bucle
done:  				end