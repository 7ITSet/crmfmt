<?
defined ('_DSITE') or die ('Access denied');

if($action=post('action')){
	switch($action){
		case 'user_login':
			$user->login();
			break;
		case 'companies_add':
			$contragents->add();
			break;
		case 'companies_change':
			$contragents->change();
			break;
		case 'm_clients_personal_add':
			$contragents->m_clients_personal_add();
			break;
		case 'm_clients_company_add':
			$contragents->m_clients_company_add();
			break;
		case 'm_clients_personal_change':
			$contragents->m_clients_personal_change();
			break;
		case 'm_clients_company_change':
			$contragents->m_clients_company_change();
			break;
		case 'categories_add':
			$services->categories_add();
			break;
		case 'm_services_add':
			$services->services_add();
			break;
		case 'm_services_change':
			$services->services_change();
			break;
		case 'm_services_group_change':
			$services->services_group_change();
			break;
		case 'products_categories_add':
			$products->categories_add();
			break;
		case 'products_add':
			$products->products_add();
			break;
		case 'products_change':
			$products->products_change();
			break;
		case 'm_products_group_change':
			$products->products_group_change();
			break;
		case 'm_users_groups_add':
			$user->group_add();
			break;
		case 'm_users_add':
			$user->user_add();
			break;
		case 'm_employees_add':
			$workers->add();
			break;
		case 'm_employees_change':
			$workers->change();
			break;
		case 'site_page_add':
			$site->page_add();
			break;
		case 'site_page_change':
			$site->page_change();
			break;
		case 'settings_page_change':
			$settings->page_change();
			break;
		case 'm_orders_add':
			$orders->orders_add();
			break;
		case 'm_orders_change':
			$orders->orders_change();
			break;
		case 'template_add':
			$document->template_add();
			break;
		case 'template_change':
			$document->template_change();
			break;
		case 'm_documents_add':
			$documents->add(array('m_documents_templates_id'=>post('m_documents_templates_id')));
			break;
		case 'm_documents_change':
			$documents->change(array('m_documents_templates_id'=>post('m_documents_templates_id')));
			break;
		case 'portfolio_foto_add':
			$orders->portfolio_foto_add();
			break;
		case 'portfolio_text_add':
			$orders->portfolio_text_add();
			break;
		case 'm_buh_pay_add':
			$buh->buh_pay_add();
			break;
		case 'm_buh_pay_change':
			$buh->buh_pay_change();
			break;
		case 'logistic_add':
			$logistic->logistic_add();
			break;
		case 'products_attributes_list_add':
			$products->products_attributes_list_add();
			break;
		case 'products_attributes_list_change':
			$products->products_attributes_list_change();
			break;
		case 'm_products_attributes_groups_add':
			$products->m_products_attributes_groups_add();
			break;
		case 'm_products_attributes_groups_change':
			$products->m_products_attributes_groups_change();
			break;
	}
}
if($action=get('action')){
	switch($action){
		/* case 'shops_main':
			$user->shops_main();
			break;
		case 'shops_delete':
			$user->shops_delete();
			break;
		case 'template_gettext':
			$document->template_gettext();
			break;
		case 'template_delete':
			$document->template_delete();
			break;
		case 'users_form':
			$user->users_form();
			break; */
		case 'document_copy':
			$documents->copy();
			break;
		case 'product_copy':
			$products->products_copy();
			break;
	}
}

if(get('email_confirm')){
	$user->login();
}
if(isset($_GET['logout'])){
	$user->logout();
}
?>