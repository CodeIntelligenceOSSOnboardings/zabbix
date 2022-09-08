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
 * @var array $data
 */

$this->includeJsFile('administration.authentication.edit.js.php');

// Authentication general fields.
$auth_tab = (new CFormGrid())
	->addItem([
		new CLabel(_('Default authentication')),
		new CFormField(
			(new CRadioButtonList('authentication_type', (int) $data['authentication_type']))
				->addValue(_x('Internal', 'authentication'), ZBX_AUTH_INTERNAL)
				->addValue(_('LDAP'), ZBX_AUTH_LDAP)
				->setModern(true)
				->removeId()
		)
	])
	->addItem(
		new CFormField(
			(new CTag('h4', true, _('Password policy')))->addClass('input-section-header')
		)
	)
	->addItem([
		new CLabel(_('Minimum password length'), 'passwd_min_length'),
		new CFormField(
			(new CNumericBox('passwd_min_length', $data['passwd_min_length'], 2, false, false, false))
				->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
		)
	])
	->addItem([
		new CLabel([
			_('Password must contain'),
			makeHelpIcon([
				_('Password requirements:'),
				(new CList([
					new CListItem([
						_('must contain at least one lowercase and one uppercase Latin letter'),
						' (', (new CSpan('A-Z'))->addClass(ZBX_STYLE_MONOSPACE_FONT), ', ',
						(new CSpan('a-z'))->addClass(ZBX_STYLE_MONOSPACE_FONT), ')'
					]),
					new CListItem([
						_('must contain at least one digit'),
						' (', (new CSpan('0-9'))->addClass(ZBX_STYLE_MONOSPACE_FONT), ')'
					]),
					new CListItem([
						_('must contain at least one special character'),
						' (', (new CSpan(' !"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~'))->addClass(ZBX_STYLE_MONOSPACE_FONT), ')'
					])
				]))->addClass(ZBX_STYLE_LIST_DASHED)
			])
		]),
		new CFormField(
			(new CList([
				(new CCheckBox('passwd_check_rules[]', PASSWD_CHECK_CASE))
					->setLabel(_('an uppercase and a lowercase Latin letter'))
					->setChecked(($data['passwd_check_rules'] & PASSWD_CHECK_CASE) == PASSWD_CHECK_CASE)
					->setUncheckedValue(0x00)
					->setId('passwd_check_rules_case'),
				(new CCheckBox('passwd_check_rules[]', PASSWD_CHECK_DIGITS))
					->setLabel(_('a digit'))
					->setChecked(($data['passwd_check_rules'] & PASSWD_CHECK_DIGITS) == PASSWD_CHECK_DIGITS)
					->setUncheckedValue(0x00)
					->setId('passwd_check_rules_digits'),
				(new CCheckBox('passwd_check_rules[]', PASSWD_CHECK_SPECIAL))
					->setLabel(_('a special character'))
					->setChecked(($data['passwd_check_rules'] & PASSWD_CHECK_SPECIAL) == PASSWD_CHECK_SPECIAL)
					->setUncheckedValue(0x00)
					->setId('passwd_check_rules_special')
			]))->addClass(ZBX_STYLE_LIST_CHECK_RADIO)
		)
	])
	->addItem([
		new CLabel([
			_('Avoid easy-to-guess passwords'),
			makeHelpIcon([
				_('Password requirements:'),
				(new CList([
					_("must not contain user's name, surname or username"),
					_('must not be one of common or context-specific passwords')
				]))->addClass(ZBX_STYLE_LIST_DASHED)
			])
		], 'passwd_check_rules_simple'),
		new CFormField(
			(new CCheckBox('passwd_check_rules[]', PASSWD_CHECK_SIMPLE))
				->setChecked(($data['passwd_check_rules'] & PASSWD_CHECK_SIMPLE) == PASSWD_CHECK_SIMPLE)
				->setUncheckedValue(0x00)
				->setId('passwd_check_rules_simple')
		)
	]);

// HTTP authentication fields.
$http_tab = (new CFormGrid())
	->addItem([
		new CLabel(_('Enable HTTP authentication'), 'http_auth_enabled'),
		new CFormField(
			(new CCheckBox('http_auth_enabled', ZBX_AUTH_HTTP_ENABLED))
				->setChecked($data['http_auth_enabled'] == ZBX_AUTH_HTTP_ENABLED)
				->setUncheckedValue(ZBX_AUTH_HTTP_DISABLED)
		)
	])
	->addItem([
		new CLabel(_('Default login form'), 'label-http-login-form'),
		new CFormField(
			(new CSelect('http_login_form'))
				->setFocusableElementId('label-http-login-form')
				->setValue($data['http_login_form'])
				->addOptions(CSelect::createOptionsFromArray([
					ZBX_AUTH_FORM_ZABBIX => _('Zabbix login form'),
					ZBX_AUTH_FORM_HTTP => _('HTTP login form')
				]))
				->setDisabled($data['http_auth_enabled'] != ZBX_AUTH_HTTP_ENABLED)
		)
	])
	->addItem([
		new CLabel(_('Remove domain name'), 'http_strip_domains'),
		new CFormField(
			(new CTextBox('http_strip_domains', $data['http_strip_domains'], false,
				DB::getFieldLength('config', 'http_strip_domains')
			))
				->setEnabled($data['http_auth_enabled'])
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		)
	])
	->addItem([
		new CLabel(_('Case-sensitive login'), 'http_case_sensitive'),
		new CFormField(
			(new CCheckBox('http_case_sensitive', ZBX_AUTH_CASE_SENSITIVE))
				->setChecked($data['http_case_sensitive'] == ZBX_AUTH_CASE_SENSITIVE)
				->setEnabled($data['http_auth_enabled'] == ZBX_AUTH_HTTP_ENABLED)
				->setUncheckedValue(ZBX_AUTH_CASE_INSENSITIVE)
		)
	]);

// LDAP authentication fields.
$ldap_tab = (new CFormGrid())
	->addItem([
		new CLabel(_('Enable LDAP authentication'), 'ldap_auth_enabled'),
		new CFormField($data['ldap_error']
			? (new CLabel($data['ldap_error']))->addClass(ZBX_STYLE_RED)
			: (new CCheckBox('ldap_auth_enabled', ZBX_AUTH_LDAP_ENABLED))
				->setChecked($data['ldap_auth_enabled'] == ZBX_AUTH_LDAP_ENABLED)
				->setUncheckedValue(ZBX_AUTH_LDAP_DISABLED)
		)
	])
	->addItem([
		new CLabel(_('Enable JIT provisioning'), 'ldap_jit_status'),
		new CFormField(
			(new CCheckBox('ldap_jit_status', ZBX_AUTH_LDAP_ENABLED))
				->setChecked($data['ldap_jit_status'] == ZBX_AUTH_LDAP_ENABLED)
				->setUncheckedValue(ZBX_AUTH_LDAP_DISABLED)
		)
	])
	->addItem([
		(new CLabel(_('Servers')))->setAsteriskMark(),
		new CFormField(
			(new CDiv(
				(new CTable())
					->setId('ldap-servers')
					->setHeader(
						(new CRowHeader([
							new CColHeader(_('Name')),
							new CColHeader(_('Host')),
							(new CColHeader(_('User groups')))->addClass(ZBX_STYLE_NOWRAP),
							_('Default'),
							''
						]))->addClass(ZBX_STYLE_GREY)
					)
					->addItem(
						(new CTag('tfoot', true))
							->addItem(
								(new CCol(
									(new CSimpleButton(_('Add')))
										->addClass(ZBX_STYLE_BTN_LINK)
										->addClass('js-add')
								))->setColSpan(5)
							)
					)->addStyle('width: 100%;')
			))
				->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
				->addStyle('min-width: '.ZBX_TEXTAREA_BIG_WIDTH.'px;')
		)
	])
	->addItem([
		new CLabel(_('Case-sensitive login'), 'ldap_case_sensitive'),
		new CFormField(
			(new CCheckBox('ldap_case_sensitive', ZBX_AUTH_CASE_SENSITIVE))
				->setChecked($data['ldap_case_sensitive'] == ZBX_AUTH_CASE_SENSITIVE)
				->setUncheckedValue(ZBX_AUTH_CASE_INSENSITIVE)
		)
	])
	->addItem([
		new CLabel(_('Provisioning period'), 'provisioning_period'),
		new CFormField(
			(new CTextBox('provisioning_period', $data['jit_provision_interval']))
				->setWidth(ZBX_TEXTAREA_4DIGITS_WIDTH)
		)
	]);

// SAML authentication fields.
$view_url = (new CUrl('zabbix.php'))
	->setArgument('action', 'authentication.edit')
	->getUrl();

$saml_tab = (new CFormGrid())
	->addItem([
		new CLabel(_('Enable SAML authentication'), 'saml_auth_enabled'),
		new CFormField($data['saml_error']
			? (new CLabel($data['saml_error']))->addClass(ZBX_STYLE_RED)
			: (new CCheckBox('saml_auth_enabled', ZBX_AUTH_SAML_ENABLED))
				->setChecked($data['saml_auth_enabled'] == ZBX_AUTH_SAML_ENABLED)
				->setUncheckedValue(ZBX_AUTH_SAML_DISABLED)
		)
	])
	->addItem([
		new CLabel(_('Enable JIT provisioning'), 'saml_jit_status'),
		new CFormField(
			(new CCheckBox('saml_jit_status', ZBX_AUTH_SCIM_PROVISIONING_ENABLED))
				->setChecked($data['saml_jit_status'] == ZBX_AUTH_SCIM_PROVISIONING_ENABLED)
				->setUncheckedValue(ZBX_AUTH_SCIM_PROVISIONING_DISABLED)
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
		)
	])
	->addItem([
		(new CLabel(_('IdP entity ID'), 'idp_entityid'))->setAsteriskMark(),
		new CFormField(
			(new CTextBox('idp_entityid', $data['idp_entityid'], false,
				DB::getFieldLength('userdirectory_saml', 'idp_entityid')
			))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAriaRequired()
		)
	])
	->addItem([
		(new CLabel(_('SSO service URL'), 'sso_url'))->setAsteriskMark(),
		new CFormField(
			(new CTextBox('sso_url', $data['sso_url'], false, DB::getFieldLength('userdirectory_saml', 'sso_url')))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAriaRequired()
		)
	])
	->addItem([
		new CLabel(_('SLO service URL'), 'slo_url'),
		new CFormField(
			(new CTextBox('slo_url', $data['slo_url'], false, DB::getFieldLength('userdirectory_saml', 'slo_url')))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		)
	])
	->addItem([
		(new CLabel(_('Username attribute'), 'username_attribute'))->setAsteriskMark(),
		new CFormField(
			(new CTextBox('username_attribute', $data['username_attribute'], false,
				DB::getFieldLength('userdirectory_saml', 'username_attribute')
			))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAriaRequired()
		)
	])
	->addItem([
		(new CLabel(_('SP entity ID'), 'sp_entityid'))->setAsteriskMark(),
		new CFormField(
			(new CTextBox('sp_entityid', $data['sp_entityid'], false,
				DB::getFieldLength('userdirectory_saml', 'sp_entityid')
			))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAriaRequired()
		)
	])
	->addItem([
		new CLabel(_('SP name ID format'), 'nameid_format'),
		new CFormField(
			(new CTextBox('nameid_format', $data['nameid_format'], false,
				DB::getFieldLength('userdirectory_saml', 'nameid_format')
			))
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAttribute('placeholder', 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient')
		)
	])
	->addItem([
		new CLabel(_('Sign')),
		new CFormField(
			(new CList([
				(new CCheckBox('sign_messages'))
					->setLabel(_('Messages'))
					->setChecked($data['sign_messages'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled'),
				(new CCheckBox('sign_assertions'))
					->setLabel(_('Assertions'))
					->setChecked($data['sign_assertions'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled'),
				(new CCheckBox('sign_authn_requests'))
					->setLabel(_('AuthN requests'))
					->setChecked($data['sign_authn_requests'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled'),
				(new CCheckBox('sign_logout_requests'))
					->setLabel(_('Logout requests'))
					->setChecked($data['sign_logout_requests'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled'),
				(new CCheckBox('sign_logout_responses'))
					->setLabel(_('Logout responses'))
					->setChecked($data['sign_logout_responses'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled')
			]))->addClass(ZBX_STYLE_LIST_CHECK_RADIO)
		)
	])
	->addItem([
		new CLabel(_('Encrypt')),
		new CFormField(
			(new CList([
				(new CCheckBox('encrypt_nameid'))
					->setLabel(_('Name ID'))
					->setChecked($data['encrypt_nameid'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled'),
				(new CCheckBox('encrypt_assertions'))
					->setLabel(_('Assertions'))
					->setChecked($data['encrypt_assertions'] == 1)
					->setUncheckedValue(0)
					->setEnabled($data['saml_enabled'])
					->addClass('saml-enabled')
			]))->addClass(ZBX_STYLE_LIST_CHECK_RADIO)
		)
	])
	->addItem([
		new CLabel(_('Case-sensitive login'), 'saml_case_sensitive'),
		new CFormField(
			(new CCheckBox('saml_case_sensitive'))
				->setChecked($data['saml_case_sensitive'] == ZBX_AUTH_CASE_SENSITIVE)
				->setUncheckedValue(ZBX_AUTH_CASE_INSENSITIVE)
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
		)
	])
	->addItem([
		new CLabel(_('Allow JIT provisioning'), 'saml_provision_status'),
		new CFormField(
			(new CCheckBox('saml_provision_status'))
				->setChecked($data['saml_provision_status'] == JIT_PROVISIONING_ENABLED)
				->setEnabled($data['saml_enabled'])
				->addClass('saml-enabled')
		)
	])
	->addItem([
		(new CLabel(_('Group name attribute'), 'saml_group_name'))
			->setAsteriskMark()
			->addClass('saml-provision-status'),
		(new CFormField(
			(new CTextBox('saml_group_name', $data['saml_group_name'], false,
				DB::getFieldLength('userdirectory_saml', 'group_name')
			))
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAriaRequired()
				->addClass('saml-enabled')
		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel(_('User name attribute'), 'saml_user_username'))->addClass('saml-provision-status'),
		(new CFormField(
			(new CTextBox('saml_user_username', $data['saml_user_username'], false,
				DB::getFieldLength('userdirectory_saml', 'user_username')
			))
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->addClass('saml-enabled')
		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel(_('User lastname attribute'), 'saml_user_lastname'))->addClass('saml-provision-status'),
		(new CFormField(
			(new CTextBox('saml_user_lastname', $data['saml_user_lastname'], false,
				DB::getFieldLength('userdirectory_saml', 'user_lastname')
			))
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->addClass('saml-enabled')
		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel(_('User group mapping')))->addClass('saml-provision-status'),
		(new CFormField(
			(new CDiv(
				(new CTable())
					->setId('saml-group-table')
					->setAttribute('style', 'width: 100%;')
					->setHeader(
						(new CRowHeader([
							'', _('SAML group pattern'), _('User groups'), _('User role'), _('Action')
						]))->addClass(ZBX_STYLE_GREY)
					)
					->addItem(
						(new CTag('tfoot', true))->addItem(
							(new CCol(
								(new CSimpleButton(_('Add')))
									->addClass(ZBX_STYLE_BTN_LINK)
									->addClass('js-add')
							))->setColSpan(5)
						)
					)
			))
				->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel([
			_('Media type mapping'),
			makeHelpIcon(
				_('Map user’s SAML media attributes (e.g. email) to Zabbix user media for sending notifications.')
			)
		]))->addClass('saml-provision-status'),
		(new CFormField(
			(new CDiv(
				(new CTable())
					->setId('saml-media-type-mapping-table')
					->setHeader(
						(new CRowHeader([
							_('Name '), _('Media type'), _('Attribute'), ''
						]))->addClass(ZBX_STYLE_GREY)
					)
					->addItem(
						(new CTag('tfoot', true))->addItem(
							(new CCol(
								(new CSimpleButton(_('Add')))
									->addClass(ZBX_STYLE_BTN_LINK)
									->addClass('js-add')
							))->setColSpan(5)
						)
					)
					->addStyle('width: 100%;')
			))
				->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)

		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel(_('Enable SCIM provisioning'), 'scim_status'))->addClass('saml-provision-status'),
		(new CFormField(
			(new CCheckBox('scim_status', ZBX_AUTH_SCIM_PROVISIONING_ENABLED))
				->setChecked($data['scim_status'] == ZBX_AUTH_SCIM_PROVISIONING_ENABLED)
				->setUncheckedValue(ZBX_AUTH_SCIM_PROVISIONING_DISABLED)
				->addClass('saml-enabled')
		))->addClass('saml-provision-status')
	])
	->addItem([
		(new CLabel(_('Authorization token'), 'scim_token'))->addClass('saml-provision-status'),
		(new CFormField(
			(new CTextBox('scim_token', $data['scim_token'], false,
				DB::getFieldLength('userdirectory_saml', 'scim_token')
			))
				->setAriaRequired()
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->addClass('saml-enabled')
		))->addClass('saml-provision-status')
	]);

$form = (new CForm())
	->addVar('action', $data['action_submit'])
	->addVar('ldap_removed_userdirectoryids', $data['ldap_removed_userdirectoryids'])
	->setId('authentication-form')
	->setAttribute('aria-labeledby', ZBX_STYLE_PAGE_TITLE)
	->disablePasswordAutofill()
	->addItem((new CTabView())
		->setSelected($data['form_refresh'] ? null : 0)
		->addTab('auth', _('Authentication'), $auth_tab)
		->addTab('http', _('HTTP settings'), $http_tab, TAB_INDICATOR_AUTH_HTTP)
		->addTab('ldap', _('LDAP settings'), $ldap_tab, TAB_INDICATOR_AUTH_LDAP)
		->addTab('saml', _('SAML settings'), $saml_tab, TAB_INDICATOR_AUTH_SAML)
		->setFooter(makeFormFooter(
			(new CSubmit('update', _('Update')))
		))
		->onTabChange('jQuery("[name=ldap_test]")[(ui.newTab.index() == 2) ? "show" : "hide"]()')
	);

(new CWidget())
	->setTitle(_('Authentication'))
	->setDocUrl(CDocHelper::getUrl(CDocHelper::USERS_AUTHENTICATION_EDIT))
	->addItem($form)
	->show();

(new CScriptTag(
	'view.init('.json_encode([
		'ldap_servers' => $data['ldap_servers'],
		'ldap_default_row_index' => $data['ldap_default_row_index'],
		'db_authentication_type' => $data['db_authentication_type'],
		'saml_provision_groups' => $data['saml_provision_groups'],
		'saml_provision_media' => $data['saml_provision_media']
	]).');'
))
	->setOnDocumentReady()
	->show();
