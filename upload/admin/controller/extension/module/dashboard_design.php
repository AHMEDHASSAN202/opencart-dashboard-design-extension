<?php
/**
 * Created by PhpStorm.
 * User: AQSSA
 */

class ControllerExtensionModuleDashboardDesign extends Controller {

    private $data = [];
    private $styles = [
        'view/stylesheet/dashboard_design.css',
        'https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700'
    ];
    private $js = [];


    public function index()
    {
        $this->load->language('extension/module/dashboard_design');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('module_dashboard_design', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->addOrDeleteEvents($this->request->post['module_dashboard_design_status']);
            $this->response->redirect($this->gUrl('marketplace/extension', ['user_token' => $this->session->data['user_token'], 'type' => 'module']));
        }

        $this->data['breadcrumbs'][] = [
            'text'  => $this->language->get('text_home'),
            'href'  => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $this->data['breadcrumbs'][] = [
            'text'  => $this->language->get('text_extension'),
            'href'  => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $this->data['breadcrumbs'][] = [
            'text'  => $this->language->get('heading_title'),
            'href'  => $this->url->link('extension/module/dashboard_design', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $this->data['action'] = $this->url->link('extension/module/dashboard_design', 'user_token=' . $this->session->data['user_token'], true);

        $this->data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->data['module_dashboard_design_status'] = (@$this->request->post['module_dashboard_design_status'] || $this->config->get('module_dashboard_design_status'));

        $this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/dashboard_design', $this->data));
    }

    private function validate()
    {

        if (!$this->hasPermission()) {
            $this->response->redirect($this->gUrl('error/permission', ['user_token' => $this->session->data['user_token']]));
        }

        if (!isset($this->request->post['module_dashboard_design_status']) ||
            !in_array($this->request->post['module_dashboard_design_status'], ['0', '1'])
        ) {
            $this->data['validation_error'] = $this->language->get('status_required');
            return false;
        }
        return true;
    }

    private function addOrDeleteEvents($status)
    {
        if ((boolean)$status) {
            $this->addEvents();
            return;
        }
        $this->deleteEvents();
    }

    public function addEvents()
    {
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent(
            'add_style_and_js_files',
            'admin/controller/common/header/before',
            'extension/module/dashboard_design/addStyleAndJsFiles'
        );

        $this->model_setting_event->addEvent(
            'remove_footer',
            'admin/controller/common/footer/after',
            'extension/module/dashboard_design/removeFooter'
        );
    }

    private function deleteEvents()
    {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('add_style_and_js_files');
        $this->model_setting_event->deleteEventByCode('remove_footer');
    }

    public function uninstall()
    {
        $this->deleteEvents();
    }

    private function hasPermission($permission = 'modify')
    {
        return $this->user->hasPermission($permission, 'extension/module/dashboard_design');
    }

    private function gUrl($link = '', $params = [])
    {
        $link .= '&';
        foreach ($params as $key => $param) {
            $link .= $key . '=' . $param . '&';
        }
        $link = rtrim($link, '&');

        return $this->url->link($link, true);
    }

    public function addStyleAndJsFiles(&$route, &$data)
    {
        $this->addStyleFiles();
        $this->addJsFiles();
    }

    private function addStyleFiles()
    {
        foreach ($this->styles as $style) {
            $this->document->addStyle($style);
        }
    }

    private function addJsFiles()
    {
        foreach ($this->js as $js) {
            $this->document->addScript($js);
        }
    }

    private function pre($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        die;
    }

    public function removeFooter(&$route, &$data, &$output)
    {
        return $output = '';
    }
}