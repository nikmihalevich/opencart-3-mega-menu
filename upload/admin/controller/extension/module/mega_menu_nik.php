<?php
class ControllerExtensionModuleMegaMenuNik extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/mega_menu_nik');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
        $this->load->model('extension/module/mega_menu_nik');
        $this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		    $post = $this->request->post;
		    if (isset($post['category'])) {
                $post['module_mega_menu_nik_categories'] = array();

                $post['module_mega_menu_nik_categories'][]  = array(
                    'category' => isset($post['category']) ? $post['category'] : '',
                    'modules'  => isset($post['modules']) ? $post['modules'] : array()
                );
            }

			$this->model_setting_setting->editSetting('module_mega_menu_nik', $post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/mega_menu_nik', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/mega_menu_nik', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_mega_menu_nik_class_button_for_call'])) {
            $data['module_mega_menu_nik_class_button_for_call'] = $this->request->post['module_mega_menu_nik_class_button_for_call'];
        } else {
            $data['module_mega_menu_nik_class_button_for_call'] = $this->config->get('module_mega_menu_nik_class_button_for_call');
        }

        if (isset($this->request->post['module_mega_menu_nik_call_type'])) {
            $data['module_mega_menu_nik_call_type'] = $this->request->post['module_mega_menu_nik_call_type'];
        } else {
            $data['module_mega_menu_nik_call_type'] = $this->config->get('module_mega_menu_nik_call_type');
        }

        if (isset($this->request->post['module_mega_menu_nik_width'])) {
            $data['module_mega_menu_nik_width'] = $this->request->post['module_mega_menu_nik_width'];
        } else {
            $data['module_mega_menu_nik_width'] = $this->config->get('module_mega_menu_nik_width');
        }

        if (isset($this->request->post['module_mega_menu_nik_width_type'])) {
            $data['module_mega_menu_nik_width_type'] = $this->request->post['module_mega_menu_nik_width_type'];
        } else {
            $data['module_mega_menu_nik_width_type'] = $this->config->get('module_mega_menu_nik_width_type');
        }

        if (isset($this->request->post['module_mega_menu_nik_link_to_all_categories'])) {
            $data['module_mega_menu_nik_link_to_all_categories'] = $this->request->post['module_mega_menu_nik_link_to_all_categories'];
        } else {
            $data['module_mega_menu_nik_link_to_all_categories'] = $this->config->get('module_mega_menu_nik_link_to_all_categories');
        }

        if (isset($this->request->post['module_mega_menu_nik_categories'])) {
            $data['module_mega_menu_nik_categories'] = $this->request->post['module_mega_menu_nik_categories'];
        } else {
            $data['module_mega_menu_nik_categories'] = $this->config->get('module_mega_menu_nik_categories');
        }

        if (!empty($data['module_mega_menu_nik_categories'])) {
            $data['added_categories'] = array();
            foreach ($data['module_mega_menu_nik_categories'] as $category) {
                $category_name = $this->model_extension_module_mega_menu_nik->getCategoryName($category['category']);
                $data['added_categories'][] = array(
                    'category_id'   => $category['category'],
                    'category_name' => $category_name['name'],
                    'modules'       => $category['modules']
                );
            }
        }


		if (isset($this->request->post['module_mega_menu_nik_status'])) {
			$data['module_mega_menu_nik_status'] = $this->request->post['module_mega_menu_nik_status'];
		} else {
			$data['module_mega_menu_nik_status'] = $this->config->get('module_mega_menu_nik_status');
		}



		$main_categories = $this->model_extension_module_mega_menu_nik->getMainCategories();

        $data['categories'] = array();

		foreach ($main_categories as $main_category) {
            $data['categories'][] = $this->model_catalog_category->getCategory($main_category['category_id']);
        }

        $data['extensions'] = array();
        $this->load->model('setting/extension');
        $this->load->model('setting/module');
        $extensions = $this->model_setting_extension->getInstalled('module');

        // Add all the modules which have multiple settings for each module
        foreach ($extensions as $code) {
            $this->load->language('extension/module/' . $code, 'extension');

            $module_data = array();

            $modules = $this->model_setting_module->getModulesByCode($code);

            foreach ($modules as $module) {
                $module_data[] = array(
                    'name' => strip_tags($module['name']),
                    'code' => $code . '.' .  $module['module_id']
                );
            }

            if ($this->config->has('module_' . $code . '_status') || $module_data) {
                $data['extensions'][] = array(
                    'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
                    'code'   => $code,
                    'module' => $module_data
                );
            }
        }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mega_menu_nik', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/mega_menu_nik')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}