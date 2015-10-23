<?php

class LoginController extends BasicController {

	private $m_admin;
	private $m_role;
  	private $adminAccount = 'superAdmin';

	private function init(){
		$this->m_admin = $this->load('Admin');
		$this->m_role  = $this->load('Role');

		$this->homeUrl = '/admin/login';
	}

	public function indexAction(){
		
	}

	public function checkLoginAction(){
		$username = $this->getPost('username');
		$password = $this->getPost('password');
		$captcha  = $this->getPost('captcha');
		
		if(!$username || !$password || !$captcha){
			jsAlert('信息不完整!');
			jsRedirect($this->homeUrl);
		}else{
			if(strtolower($captcha) != strtolower($this->getSession('adminCaptcha'))){
				jsAlert('验证码不正确!');
				jsRedirect($this->homeUrl);
			}
		}
		
		// 管理员登陆
		if($this->adminAccount == $username){
			$data = $this->m_admin->checkLogin($username, $password);
		}else{
			// 普通角色登陆
			$data = $this->m_role->checkRole($username, $password);
		}
		if(!$data){
			// Login fail
			$log['status'] = 0;
			jsAlert('账号或密码不正确!');
			jsRedirect($this->homeUrl);
		}else{
			// Login OK, log this action and find privileges
			$this->setSession('adminID', $data['id']);
			$this->setSession('adminName', $username);
			
			// admin 拥有所有的权限
			if($this->adminAccount == $username){
				$this->setSession('priv', 'ALL');
			}else{
				// 不是管理员, 记录其 roleID, 用于查找权限
				// 1: 取得登录的角色所拥有的权限
				$priv = $this->m_role->getPrivilegeByRoleID($data['id']);
				
				// 如果角色没有分配到任何权限, 提示, 并且退出, 有则 SESSION 记录其所有的权限，供进一步处理
				if(!$priv['privilege']){
					jsAlert('您还没有任何权限, 请联系管理员!');
					jsRedirect($this->homeUrl);
				}else{
					$this->setSession('priv', $priv);
				}
			}
		}
		
		jsRedirect('/admin/index/main');
	}

	public function logoutAction(){
		$this->unsetSession('adminID');
		$this->unsetSession('adminName');

		jsRedirect($this->homeUrl);
	}
}
