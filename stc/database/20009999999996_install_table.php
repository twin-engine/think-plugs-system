<?php

use think\migration\Migrator;

@set_time_limit(0);
@ini_set('memory_limit', -1);

class InstallTable extends Migrator {

	/**
	 * 创建数据库
	 */
	 public function change() {
		$this->_create_sys_app();
		$this->_create_sys_config();
		$this->_create_sys_dept();
		$this->_create_sys_dict_data();
		$this->_create_sys_dict_type();
		$this->_create_sys_email();
		$this->_create_sys_green();
		$this->_create_sys_industry();
		$this->_create_sys_menu();
		$this->_create_sys_post();
		$this->_create_sys_region();
		$this->_create_sys_role();
		$this->_create_sys_role_dept();
		$this->_create_sys_role_menu();
		$this->_create_sys_sms();
		$this->_create_sys_tenant();
		$this->_create_sys_tenant_app();
		$this->_create_sys_tenant_menu();
		$this->_create_sys_upload_file();
		$this->_create_sys_upload_group();
		$this->_create_sys_user_post();
		$this->_create_sys_user_role();
		$this->_create_system_base();
		$this->_create_system_config();
		$this->_create_system_data();
		$this->_create_system_oplog();
		$this->_create_system_queue();
		$this->_create_system_user();

	}

    /**
     * 创建数据对象
     * @class SysApp
     * @table sys_app
     * @return void
     */
    private function _create_sys_app() {

        // 当前数据表
        $table = 'sys_app';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统应用表',
        ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => NULL, 'null' => false, 'comment' => '应用名称' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '编码' ])
		->addColumn('active','string',[ 'limit' => 1, 'default' => NULL, 'null' => true, 'comment' => '是否默认激活（Y-是，N-否）' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => true, 'comment' => '应用排序' ])
		->addColumn('status','integer',[ 'default' => '0', 'null' => false, 'comment' => '状态（字典 0正常 1停用 ）' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0正常1删除' ])
		->addColumn('created_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysConfig
     * @table sys_config
     * @return void
     */
    private function _create_sys_config() {

        // 当前数据表
        $table = 'sys_config';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-参数配置表',
        ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => NULL, 'null' => false, 'comment' => '名称' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '编码' ])
		->addColumn('value','text',[ 'default' => NULL, 'null' => false, 'comment' => '值' ])
		->addColumn('sys_flag','string',[ 'limit' => 1, 'default' => NULL, 'null' => false, 'comment' => '是否是系统参数（Y-是，N-否）' ])
		->addColumn('type','string',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '参数值类型0为普通1为敏感' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->addColumn('status','integer',[ 'default' => NULL, 'null' => false, 'comment' => '状态（字典 0正常 1停用 2删除）' ])
		->addColumn('group_code','string',[ 'limit' => 255, 'default' => 'DEFAULT', 'null' => false, 'comment' => '常量所属分类的编码，来自于“常量的分类”字典' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => false, 'comment' => '状态（字典 0正常 1删除）' ])
		->addColumn('created_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '创建时间' ])
		->addColumn('create_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('deleted_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysDept
     * @table sys_dept
     * @return void
     */
    private function _create_sys_dept() {

        // 当前数据表
        $table = 'sys_dept';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-部门信息表',
        ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->addColumn('parent_id','integer',[ 'limit' => 11,'default' => NULL, 'null' => true, 'comment' => '父ID' ])
		->addColumn('level','string',[ 'limit' => 500, 'default' => NULL, 'null' => true, 'comment' => '组级集合' ])
		->addColumn('name','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '部门名称' ])
		->addColumn('leader','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '负责人' ])
		->addColumn('phone','string',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '联系电话' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '状态 (0正常 1停用)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0未删1已删' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => true, 'comment' => '排序' ])
		->addColumn('created_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建者' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '更新者' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->addIndex('parent_id', [ 'name' => 'idx_sys_dept_parent_id' ])
		->addIndex('tenant_id', [ 'name' => 'idx_sys_dept_tenant_id' ])
		->addIndex('is_deleted', [ 'name' => 'idx_sys_dept_is_deleted' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysDictData
     * @table sys_dict_data
     * @return void
     */
    private function _create_sys_dict_data() {

        // 当前数据表
        $table = 'sys_dict_data';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统字典值表',
        ])
		->addColumn('type_id','integer',[ 'default' => NULL, 'null' => false, 'comment' => '字典类型id' ])
		->addColumn('value','text',[ 'default' => NULL, 'null' => false, 'comment' => '值' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '编码' ])
		->addColumn('sort','integer',[ 'default' => NULL, 'null' => false, 'comment' => '排序' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '状态（字典 0正常 1停用 2删除）' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('created_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '更新人' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysDictType
     * @table sys_dict_type
     * @return void
     */
    private function _create_sys_dict_type() {

        // 当前数据表
        $table = 'sys_dict_type';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统字典类型表',
        ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => NULL, 'null' => false, 'comment' => '名称' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '编码' ])
		->addColumn('sort','integer',[ 'default' => NULL, 'null' => false, 'comment' => '排序' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '状态（字典 0正常 1停用 2删除）' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('created_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '更新人' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysEmail
     * @table sys_email
     * @return void
     */
    private function _create_sys_email() {

        // 当前数据表
        $table = 'sys_email';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-邮件发送表',
        ])
		->addColumn('type','integer',[ 'limit' => 1, 'default' => 1, 'null' => true, 'comment' => '邮件类型' ])
		->addColumn('email','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '目标邮箱' ])
		->addColumn('code','integer',[ 'limit' => 6, 'default' => NULL, 'null' => true, 'comment' => '验证码' ])
		->addColumn('result','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '返回结果' ])
		->addColumn('content','string',[ 'limit' => 512, 'default' => '', 'null' => true, 'comment' => '邮件内容' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '邮件状态(0失败,1成功)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => false, 'comment' => '状态（字典 0正常 1删除）' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('create_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('deleted_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->addIndex('type', [ 'name' => 'idx_sys_email_type' ])
		->addIndex('status', [ 'name' => 'idx_sys_email_status' ])
		->addIndex('email', [ 'name' => 'idx_sys_email_email' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysGreen
     * @table sys_green
     * @return void
     */
    private function _create_sys_green() {

        // 当前数据表
        $table = 'sys_green';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-内容安全检测',
        ])
		->addColumn('field_id','integer',[ 'default' => NULL, 'null' => true, 'comment' => '字段ID' ])
		->addColumn('tenant_id','integer',[ 'limit' => 20,'default' => NULL, 'null' => false, 'comment' => '租户id' ])
		->addColumn('create_user_id','integer',[ 'default' => NULL, 'null' => true, 'comment' => '提交人ID' ])
		->addColumn('data_id','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '安全检测编号' ])
		->addColumn('content','text',[ 'default' => NULL, 'null' => true, 'comment' => '待检内容' ])
		->addColumn('app','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '来源应用' ])
		->addColumn('label','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '返回结果label' ])
		->addColumn('rate','string',[ 'limit' => 5, 'default' => '', 'null' => true, 'comment' => '返回结果rate' ])
		->addColumn('scene','string',[ 'limit' => 10, 'default' => '', 'null' => true, 'comment' => '检测方式' ])
		->addColumn('suggestion','string',[ 'limit' => 10, 'default' => '', 'null' => true, 'comment' => '返回结果suggestion' ])
		->addColumn('task_id','string',[ 'limit' => 60, 'default' => '', 'null' => true, 'comment' => '检测taskId' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '状态(0失败,1成功)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => false, 'comment' => '状态（字典 0正常 1删除）' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addIndex('field_id', [ 'name' => 'idx_sys_green_field_id' ])
		->addIndex('create_user_id', [ 'name' => 'idx_sys_green_create_user_id' ])
		->addIndex('suggestion', [ 'name' => 'idx_sys_green_suggestion' ])
		->addIndex('scene', [ 'name' => 'idx_sys_green_scene' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysIndustry
     * @table sys_industry
     * @return void
     */
    private function _create_sys_industry() {

        // 当前数据表
        $table = 'sys_industry';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-行业-分类',
        ])
		->addColumn('parent_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '上级行业分类' ])
		->addColumn('title','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '行业分类名称' ])
		->addColumn('desc','string',[ 'limit' => 1024, 'default' => '', 'null' => true, 'comment' => '行业分类描述' ])
		->addColumn('standard','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '是否有国家标准' ])
		->addColumn('standard_no','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '标准号' ])
		->addColumn('standard_file','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '国标文件' ])
		->addColumn('status','string',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '状态' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '0', 'null' => true, 'comment' => '排序权重' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '删除状态' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '创建时间' ])
		->addColumn('created_by','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '创者人' ])
		->addColumn('updated_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '更新时间' ])
		->addColumn('updated_by','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '更新人' ])
		->addColumn('deleted_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '删除时间' ])
		->addColumn('deleted_by','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '删除人' ])
		->addIndex('parent_id', [ 'name' => 'idx_sys_industry_parent_id' ])
		->addIndex('is_deleted', [ 'name' => 'idx_sys_industry_is_deleted' ])
		->addIndex('status', [ 'name' => 'idx_sys_industry_status' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysMenu
     * @table sys_menu
     * @return void
     */
    private function _create_sys_menu() {

        // 当前数据表
        $table = 'sys_menu';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统菜单表',
        ])
		->addColumn('parent_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '父id' ])
		->addColumn('level','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '组级集合' ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => NULL, 'null' => false, 'comment' => '名称' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '编码' ])
		->addColumn('type','integer',[ 'default' => '1', 'null' => false, 'comment' => '菜单类型（字典 0目录 1菜单 2按钮）' ])
		->addColumn('icon','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '图标' ])
		->addColumn('router','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '路由地址' ])
		->addColumn('component','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '组件地址' ])
		->addColumn('permission','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '权限标识' ])
		->addColumn('application','string',[ 'limit' => 50, 'default' => NULL, 'null' => false, 'comment' => '应用分类（应用编码）' ])
		->addColumn('open_type','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '打开方式（字典 0无 1组件 2内链 3外链）' ])
		->addColumn('visible','string',[ 'limit' => 1, 'default' => 'Y', 'null' => false, 'comment' => '是否可见（Y-是，N-否）' ])
		->addColumn('link','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '链接地址' ])
		->addColumn('redirect','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '重定向地址' ])
		->addColumn('weight','integer',[ 'default' => '1', 'null' => true, 'comment' => '权重（字典 1系统权重 2业务权重）' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => false, 'comment' => '排序' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '状态（字典 0正常 1停用 2删除）' ])
		->addColumn('hide','integer',[ 'default' => '0', 'null' => true, 'comment' => '隐藏部分未启用的及行业特殊的1隐藏' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '0正常1删除' ])
		->addColumn('created_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '创建者' ])
		->addColumn('updated_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '更新者' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysPost
     * @table sys_post
     * @return void
     */
    private function _create_sys_post() {

        // 当前数据表
        $table = 'sys_post';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-岗位信息表',
        ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->addColumn('name','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '岗位名称' ])
		->addColumn('code','string',[ 'limit' => 100, 'default' => NULL, 'null' => true, 'comment' => '岗位代码' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '状态 (0正常 1停用)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0未删1已删' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => true, 'comment' => '排序' ])
		->addColumn('created_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '创建者' ])
		->addColumn('updated_by','integer',[ 'default' => NULL, 'null' => true, 'comment' => '更新者' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('remark','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '备注' ])
		->addIndex('tenant_id', [ 'name' => 'idx_sys_post_tenant_id' ])
		->addIndex('is_deleted', [ 'name' => 'idx_sys_post_is_deleted' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysRegion
     * @table sys_region
     * @return void
     */
    private function _create_sys_region() {

        // 当前数据表
        $table = 'sys_region';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '省市区数据表',
        ])
		->addColumn('name','string',[ 'limit' => 255, 'default' => '', 'null' => false, 'comment' => '区划名称' ])
		->addColumn('pid','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '父级ID' ])
		->addColumn('code','string',[ 'limit' => 255, 'default' => '', 'null' => false, 'comment' => '区划编码' ])
		->addColumn('level','integer',[ 'limit' => 11, 'default' => '1', 'null' => false, 'comment' => '层级(1省级 2市级 3区/县级)' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysRole
     * @table sys_role
     * @return void
     */
    private function _create_sys_role() {

        // 当前数据表
        $table = 'sys_role';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-角色信息表',
        ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->addColumn('name','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '角色名称' ])
		->addColumn('code','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '角色代码' ])
		->addColumn('data_scope','string',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '数据范围（0：全部数据权限 1：自定义数据权限 2：本部门数据权限 3：本部门及以下数据权限 4：本人数据权限）' ])
		->addColumn('remark','string',[ 'limit' => 500, 'default' => '', 'null' => true, 'comment' => '备注说明' ])
		->addColumn('created_by','integer',[ 'default' => '0', 'null' => true, 'comment' => '创建者' ])
		->addColumn('updated_by','integer',[ 'default' => '0', 'null' => true, 'comment' => '更新者' ])
		->addColumn('sort','integer',[ 'default' => '0', 'null' => true, 'comment' => '排序权重' ])
		->addColumn('status','string',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '权限状态(0使用,1禁用)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0未删1已删' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addIndex('name', [ 'name' => 'idx_sys_role_name' ])
		->addIndex('tenant_id', [ 'name' => 'idx_sys_role_tenant_id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysRoleDept
     * @table sys_role_dept
     * @return void
     */
    private function _create_sys_role_dept() {

        // 当前数据表
        $table = 'sys_role_dept';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-角色与部门关联表',
        ])
		->addColumn('dept_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '部门' ])
		->addColumn('role_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '角色' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysRoleMenu
     * @table sys_role_menu
     * @return void
     */
    private function _create_sys_role_menu() {

        // 当前数据表
        $table = 'sys_role_menu';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-角色与菜单关联表',
        ])
		->addColumn('menu_id','integer',[ 'limit' => 11,'default' => NULL, 'null' => true, 'comment' => '菜单' ])
		->addColumn('role_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '角色' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysSms
     * @table sys_sms
     * @return void
     */
    private function _create_sys_sms() {

        // 当前数据表
        $table = 'sys_sms';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-短信发送表',
        ])
		->addColumn('type','integer',[ 'limit' => 1, 'default' => 1, 'null' => true, 'comment' => '短信类型' ])
		->addColumn('msgid','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '消息编号' ])
		->addColumn('phone','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '目标手机' ])
		->addColumn('code','integer',[ 'limit' => 6, 'default' => NULL, 'null' => true, 'comment' => '验证码' ])
		->addColumn('region','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '国家编号' ])
		->addColumn('result','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '返回结果' ])
		->addColumn('content','string',[ 'limit' => 512, 'default' => '', 'null' => true, 'comment' => '短信内容' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '短信状态(0失败,1成功)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => false, 'comment' => '状态（字典 0正常 1删除）' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('create_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('updated_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('deleted_at','datetime',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->addIndex('type', [ 'name' => 'idx_sys_sms_type' ])
		->addIndex('status', [ 'name' => 'idx_sys_sms_status' ])
		->addIndex('phone', [ 'name' => 'idx_sys_sms_phone' ])
		->addIndex('msgid', [ 'name' => 'idx_sys_sms_msgid' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysTenant
     * @table sys_tenant
     * @return void
     */
    private function _create_sys_tenant() {

        // 当前数据表
        $table = 'sys_tenant';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '租户表',
        ])
		->addColumn('cid','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '别名ID自增' ])
		->addColumn('parent_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '上级id' ])
		->addColumn('industry_id','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '所属行业分类' ])
		->addColumn('level','string',[ 'limit' => 500, 'default' => '0', 'null' => false, 'comment' => '集合' ])
		->addColumn('name','string',[ 'limit' => 50, 'default' => '', 'null' => false, 'comment' => '租户名称' ])
		->addColumn('code','string',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '编码' ])
		->addColumn('license_no','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '统一社会信用代码' ])
		->addColumn('legal_representative','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '法人' ])
		->addColumn('corporate_ID_card','string',[ 'limit' => 18, 'default' => NULL, 'null' => true, 'comment' => '法人身份证' ])
		->addColumn('contacts','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '联系人' ])
		->addColumn('contact_number','string',[ 'limit' => 20, 'default' => '', 'null' => false, 'comment' => '联系电话' ])
		->addColumn('contact_tel','string',[ 'limit' => 18, 'default' => NULL, 'null' => true, 'comment' => '企业固话' ])
		->addColumn('bank_name','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '开户行' ])
		->addColumn('bank_no','string',[ 'limit' => 30, 'default' => NULL, 'null' => true, 'comment' => '对公账户' ])
		->addColumn('business_license','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '营业执照' ])
		->addColumn('qualification_documents','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '资质文件' ])
		->addColumn('remark','string',[ 'limit' => 1000, 'default' => '', 'null' => true, 'comment' => '租户描述' ])
		->addColumn('address','string',[ 'limit' => 500, 'default' => '', 'null' => true, 'comment' => '地址' ])
		->addColumn('package_id','integer',[ 'limit' => 1, 'default' => '1', 'null' => false, 'comment' => '套餐包ID' ])
		->addColumn('gas_total','integer',[ 'limit' => 20, 'default' => '0', 'null' => true, 'comment' => 'gas总量' ])
		->addColumn('gas_used','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '消费的gas量' ])
		->addColumn('start_time','datetime',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '租赁开始时间' ])
		->addColumn('end_time','datetime',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '租赁结束时间' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 0, 'null' => false, 'comment' => '0-正常，1-已禁用' ])
		->addColumn('progress','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '信息完善进度0无信息1已完善未认证2认证失败3认证成功需输入验证金额4成功' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => false, 'comment' => '排序' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '0:未删除，1-已删除' ])
		->addColumn('created_by','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_by','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '更新人' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '更新时间' ])
		->addColumn('deleted_at','string',[ 'limit' => 255, 'default' => NULL, 'null' => true, 'comment' => '' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysTenantApp
     * @table sys_tenant_app
     * @return void
     */
    private function _create_sys_tenant_app() {

        // 当前数据表
        $table = 'sys_tenant_app';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-租户与应用关联表',
        ])
		->addColumn('app_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => 'app标识' ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysTenantMenu
     * @table sys_tenant_menu
     * @return void
     */
    private function _create_sys_tenant_menu() {

        // 当前数据表
        $table = 'sys_tenant_menu';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-租户与菜单关联表',
        ])
		->addColumn('menu_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '菜单' ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysUploadFile
     * @table sys_upload_file
     * @return void
     */
    private function _create_sys_upload_file() {

        // 当前数据表
        $table = 'sys_upload_file';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '文件库记录表',
        ])
		->addColumn('group_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '分组id' ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => '0', 'null' => false, 'comment' => '租户编号' ])
		->addColumn('storage','string',[ 'limit' => 10, 'default' => '', 'null' => false, 'comment' => '存储方式' ])
		->addColumn('domain','string',[ 'limit' => 255, 'default' => NULL, 'null' => false, 'comment' => '文件上传域名' ])
		->addColumn('url','string',[ 'limit' => 500, 'default' => NULL, 'null' => false, 'comment' => '' ])
		->addColumn('type','integer',[ 'default' => '10', 'null' => false, 'comment' => '文件类型(10图片 20附件 30视频)' ])
		->addColumn('name','string',[ 'limit' => 255, 'default' => '', 'null' => false, 'comment' => '文件名称(仅显示)' ])
		->addColumn('path','string',[ 'limit' => 255, 'default' => '', 'null' => false, 'comment' => '文件路径' ])
		->addColumn('size','integer',[ 'default' => '0', 'null' => false, 'comment' => '文件大小(字节)' ])
		->addColumn('ext','string',[ 'limit' => 20, 'default' => '', 'null' => false, 'comment' => '文件扩展名' ])
		->addColumn('uploader_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '上传者用户ID' ])
		->addColumn('is_recycle','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '是否在回收站' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => '0', 'null' => false, 'comment' => '状态0正常1禁用' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0正常1删除' ])
		->addColumn('created_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->addIndex('group_id', [ 'name' => 'idx_sys_upload_file_group_id' ])
		->addIndex('uploader_id', [ 'name' => 'idx_sys_upload_file_uploader_id' ])
		->addIndex('is_recycle', [ 'name' => 'idx_sys_upload_file_is_recycle' ])
		->addIndex('status', [ 'name' => 'idx_sys_upload_file_status' ])
		->addIndex('is_deleted', [ 'name' => 'idx_sys_upload_file_is_deleted' ])
		->addIndex('tenant_id', [ 'name' => 'idx_sys_upload_file_tenant_id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysUploadGroup
     * @table sys_upload_group
     * @return void
     */
    private function _create_sys_upload_group() {

        // 当前数据表
        $table = 'sys_upload_group';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '文件库分组记录表',
        ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => '0', 'null' => false, 'comment' => '租户编号' ])
		->addColumn('name','string',[ 'limit' => 30, 'default' => '', 'null' => false, 'comment' => '分组名称' ])
		->addColumn('parent_id','integer',[ 'limit' => 11, 'default' => '0', 'null' => false, 'comment' => '上级分组ID' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '100', 'null' => false, 'comment' => '排序(数字越小越靠前)' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '0正常1删除' ])
		->addColumn('created_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '创建人' ])
		->addColumn('updated_by','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '修改人' ])
		->addColumn('created_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addColumn('updated_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '修改时间' ])
		->addColumn('deleted_at','timestamp',[ 'default' => NULL, 'null' => true, 'comment' => '删除时间' ])
		->addIndex('tenant_id', [ 'name' => 'idx_sys_upload_group_tenant_id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysUserPost
     * @table sys_user_post
     * @return void
     */
    private function _create_sys_user_post() {

        // 当前数据表
        $table = 'sys_user_post';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-用户与岗位关联表',
        ])
		->addColumn('user_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '用户' ])
		->addColumn('post_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '岗位' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SysUserRole
     * @table sys_user_role
     * @return void
     */
    private function _create_sys_user_role() {

        // 当前数据表
        $table = 'sys_user_role';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-用户与角色关联表',
        ])
		->addColumn('user_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '用户' ])
		->addColumn('role_id','integer',[ 'limit' => 11, 'default' => NULL, 'null' => true, 'comment' => '角色' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemBase
     * @table system_base
     * @return void
     */
    private function _create_system_base() {

        // 当前数据表
        $table = 'system_base';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-字典',
        ])
		->addColumn('type','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '数据类型' ])
		->addColumn('code','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '数据代码' ])
		->addColumn('name','string',[ 'limit' => 200, 'default' => '', 'null' => true, 'comment' => '数据名称' ])
		->addColumn('content','text',[ 'default' => NULL, 'null' => true, 'comment' => '数据内容' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '0', 'null' => true, 'comment' => '排序权重' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 1, 'null' => true, 'comment' => '数据状态(0禁用,1启动)' ])
		->addColumn('deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0正常,1已删)' ])
		->addColumn('deleted_at','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '删除时间' ])
		->addColumn('deleted_by','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '删除用户' ])
		->addColumn('create_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addIndex('type', [ 'name' => 'idx_system_base_type' ])
		->addIndex('code', [ 'name' => 'idx_system_base_code' ])
		->addIndex('name', [ 'name' => 'idx_system_base_name' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemConfig
     * @table system_config
     * @return void
     */
    private function _create_system_config() {

        // 当前数据表
        $table = 'system_config';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-配置',
        ])
		->addColumn('type','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '配置分类' ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '配置名称' ])
		->addColumn('value','string',[ 'limit' => 2048, 'default' => '', 'null' => true, 'comment' => '配置内容' ])
		->addIndex('type', [ 'name' => 'idx_system_config_type' ])
		->addIndex('name', [ 'name' => 'idx_system_config_name' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemData
     * @table system_data
     * @return void
     */
    private function _create_system_data() {

        // 当前数据表
        $table = 'system_data';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-数据',
        ])
		->addColumn('name','string',[ 'limit' => 100, 'default' => '', 'null' => true, 'comment' => '配置名' ])
		->addColumn('value','text',[ 'default' => NULL, 'null' => true, 'comment' => '配置值' ])
		->addIndex('name', [ 'name' => 'idx_system_data_name' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemOplog
     * @table system_oplog
     * @return void
     */
    private function _create_system_oplog() {

        // 当前数据表
        $table = 'system_oplog';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-日志',
        ])
		->addColumn('node','string',[ 'limit' => 200, 'default' => '', 'null' => false, 'comment' => '当前操作节点' ])
		->addColumn('geoip','string',[ 'limit' => 15, 'default' => '', 'null' => false, 'comment' => '操作者IP地址' ])
		->addColumn('action','string',[ 'limit' => 200, 'default' => '', 'null' => false, 'comment' => '操作行为名称' ])
		->addColumn('content','string',[ 'limit' => 1024, 'default' => '', 'null' => false, 'comment' => '操作内容描述' ])
		->addColumn('username','string',[ 'limit' => 50, 'default' => '', 'null' => false, 'comment' => '操作人用户名' ])
		->addColumn('create_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '创建时间' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemQueue
     * @table system_queue
     * @return void
     */
    private function _create_system_queue() {

        // 当前数据表
        $table = 'system_queue';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-任务',
        ])
		->addColumn('code','string',[ 'limit' => 20, 'default' => '', 'null' => false, 'comment' => '任务编号' ])
		->addColumn('title','string',[ 'limit' => 100, 'default' => '', 'null' => false, 'comment' => '任务名称' ])
		->addColumn('command','string',[ 'limit' => 500, 'default' => '', 'null' => true, 'comment' => '执行指令' ])
		->addColumn('exec_pid','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '执行进程' ])
		->addColumn('exec_data','text',[ 'default' => NULL, 'null' => true, 'comment' => '执行参数' ])
		->addColumn('exec_time','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '执行时间' ])
		->addColumn('exec_desc','string',[ 'limit' => 500, 'default' => '', 'null' => true, 'comment' => '执行描述' ])
		->addColumn('enter_time','decimal',[ 'precision' => 20, 'scale' => 4, 'default' => '0.0000', 'null' => true, 'comment' => '开始时间' ])
		->addColumn('outer_time','decimal',[ 'precision' => 20, 'scale' => 4, 'default' => '0.0000', 'null' => true, 'comment' => '结束时间' ])
		->addColumn('loops_time','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '循环时间' ])
		->addColumn('attempts','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '执行次数' ])
		->addColumn('rscript','integer',[ 'limit' => 1, 'default' => 1, 'null' => true, 'comment' => '任务类型(0单例,1多例)' ])
		->addColumn('status','integer',[ 'limit' => 1, 'default' => 1, 'null' => true, 'comment' => '任务状态(1新任务,2处理中,3成功,4失败)' ])
		->addColumn('create_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => false, 'comment' => '创建时间' ])
		->addIndex('code', [ 'name' => 'idx_system_queue_code' ])
		->addIndex('title', [ 'name' => 'idx_system_queue_title' ])
		->addIndex('status', [ 'name' => 'idx_system_queue_status' ])
		->addIndex('rscript', [ 'name' => 'idx_system_queue_rscript' ])
		->addIndex('create_at', [ 'name' => 'idx_system_queue_create_at' ])
		->addIndex('exec_time', [ 'name' => 'idx_system_queue_exec_time' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

    /**
     * 创建数据对象
     * @class SystemUser
     * @table system_user
     * @return void
     */
    private function _create_system_user() {

        // 当前数据表
        $table = 'system_user';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '系统-用户',
        ])
		->addColumn('tenant_id','integer',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '租户id' ])
		->addColumn('user_type','string',[ 'limit' => 20, 'default' => '200', 'null' => true, 'comment' => '用户类型100系统管理员超管200租户管理员300代理商管理员' ])
		->addColumn('username','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '用户账号' ])
		->addColumn('realname','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '用户实名' ])
		->addColumn('password','string',[ 'limit' => 32, 'default' => '', 'null' => true, 'comment' => '用户密码' ])
		->addColumn('nickname','string',[ 'limit' => 50, 'default' => '', 'null' => true, 'comment' => '用户昵称' ])
		->addColumn('headimg','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '头像地址' ])
		->addColumn('dashboard','string',[ 'limit' => 50, 'default' => NULL, 'null' => true, 'comment' => '' ])
		->addColumn('dept_id','string',[ 'limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '部门ID' ])
		->addColumn('contact_qq','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '联系QQ' ])
		->addColumn('contact_mail','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '联系邮箱' ])
		->addColumn('contact_phone','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '联系手机' ])
		->addColumn('login_ip','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '登录地址' ])
		->addColumn('login_at','string',[ 'limit' => 20, 'default' => '', 'null' => true, 'comment' => '登录时间' ])
		->addColumn('login_num','integer',[ 'limit' => 11, 'default' => '0', 'null' => true, 'comment' => '登录次数' ])
		->addColumn('describe','string',[ 'limit' => 255, 'default' => '', 'null' => true, 'comment' => '备注说明' ])
		->addColumn('status','string',[ 'limit' => 1, 'default' => '0', 'null' => true, 'comment' => '状态(0禁用,1启用)' ])
		->addColumn('sort','integer',[ 'limit' => 3, 'default' => '0', 'null' => true, 'comment' => '排序权重' ])
		->addColumn('is_deleted','integer',[ 'limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除(1删除,0未删)' ])
		->addColumn('create_at','timestamp',[ 'default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间' ])
		->addIndex('status', [ 'name' => 'idx_system_user_status' ])
		->addIndex('username', [ 'name' => 'idx_system_user_username' ])
		->addIndex('is_deleted', [ 'name' => 'idx_system_user_is_deleted' ])
		->addIndex('tenant_id', [ 'name' => 'idx_system_user_tenant_id' ])
		->save();

		// 修改主键长度
		$this->table($table)->changeColumn('id','integer',['limit'=>20,'identity'=>true]);
	}

}
