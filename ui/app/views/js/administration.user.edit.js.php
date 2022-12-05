<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 */
?>

<script type="text/javascript">
	const view = new class {

		init() {
			document.getElementById('user-form').addEventListener('submit', (e) => {
				if (!this._userFormSubmit()) {
					e.preventDefault();
				}
			});

			$('#roleid').change(function() {
				if (this.querySelectorAll('[name="roleid"]').length > 0) {
					document.getElementById('user-form').submit();
				}
			});
		}

		_userFormSubmit() {
			const fields_to_trim = ['#username', '#name', '#surname', '#autologout', '#refresh', '#url'];
			document.querySelectorAll(fields_to_trim.join(', ')).forEach((elem) => {
				elem.value = elem.value.trim();
			});

			const password1 = document.getElementById('password1').value;
			const password2 = document.getElementById('password2').value;

			if (password1 !== '' && password2 !== '') {
				const warning_msg = <?= json_encode(
					_('In case of successful password change user will be logged out of all active sessions. Continue?')
				) ?>;

				return confirm(warning_msg);
			}
			else {
				return true;
			}
		}
	}
</script>
