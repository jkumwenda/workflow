<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AllTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*************************
         * users
         ************************* */
        $this->command->line("users");
        $olds = DB::connection('mysql_old')->select('select * from tbl_user');
        $news = [];
        foreach ($olds as $old) {
            $news[] = [
              'id' => $old->PK_USERID,
              'username' => $old->USERNAME,
              'first_name' => $old->FIRSTNAME,
              'surname' => $old->SURNAME,
              'salutation' => $old->SALUTATION,
              'email' => $old->EMAILADDRESS,
              'email_verified_at' => null,
              'password' => $old->PASSWORD,
              'remember_token' => null,
              'signature' => $old->SIGNATURE,
              'active' => $old->ACTIVE,
              'created_at' => null,
              'updated_at' => null
            ];
        };
        DB::table('users')->insert($news);

        /*************************
         * companies
         ************************* */
        $this->command->line("companies");
        $olds = DB::connection('mysql_old')->select('select * from tbl_company where PK_COMPANYID != 12 order by PK_COMPANYID');
        $news = [];
        foreach ($olds as $old) {
            $news[] = [
                'id' => $old->PK_COMPANYID,
                'name' => $old->NAME,
                'created_at' => null,
                'updated_at' => null
            ];
        };
        DB::table('companies')->insert($news);

        /*************************
         * units
         ************************* */
        $this->command->line("units");
        $olds = DB::connection('mysql_old')->select('select * from tbl_unit');
        $news = [];
        foreach ($olds as $old) {
            $news[] = [
                'id' => $old->PK_UNITID,
                'code' => $old->UNIT_CODE,
                'name' => $old->UNIT_NAME,
                'company_id' => $old->FK_COMPANYID,
                'category' => $old->CATEGORY,
                'db_name' => $old->COMPANYCODE,
                'created_at' => null,
                'updated_at' => null
            ];
        };
        DB::table('units')->insert($news);

        /*************************
         * roles
         ************************* */
        $this->command->line("roles");
        $olds = DB::connection('mysql_old')->select('select * from tbl_role');
        $news = [];
        foreach ($olds as $old) {
            $searchLevel = 'All';
            if (in_array($old->DESCRIPTION, [
                'Head of department',
                'RSC Director',
                'Project accountant',
                'Director',
                'Project manager',
                'Checking office',
                'Project Investigator']) !== false) {
                $searchLevel = 'Unit';
            } else if (in_array($old->DESCRIPTION, [
                'Member',
            ]) !== false) {
                $searchLevel = 'Own';
            }

            $news[] = [
                'id' => $old->PK_ROLEID,
                'short_name' => $old->SHORTNAME,
                'description' => $old->DESCRIPTION,
                'single_user' => $old->EXISTONCE == 1 ? 1 : 0,
                'search_level' => $searchLevel,
                'created_at' => null,
                'updated_at' => null
            ];
        };
        DB::table('roles')->insert($news);
        // Accountant, flag
        DB::table('roles')->where('id', 14)->update(['single_user' => '1']);

        /*************************
         * RoleUser
         ************************* */
        $this->command->line("RoleUser");
        $olds = DB::connection('mysql_old')->select('select * from tbl_userrole');
        $news = [];
        foreach ($olds as $old) {
            $news[] = [
                'user_id' => $old->FK_USERID,
                'unit_id' => $old->FK_UNITID,
                'role_id' => $old->FK_ROLEID,
                'is_default' => $old->IS_DEFAULT,
                'created_at' => null,
                'updated_at' => null
            ];
        };
        DB::table('role_user')->insert($news);

        /*************************
         * UserUnit (Assistant Account for each units)
         * -> Integrate with RoleUser
         ************************* */
        $this->command->line("UserUnit (Integrate with RoleUser)");
        // $olds = DB::connection('mysql_old')->select('select uu.FK_UNITID, uu.FK_USERID, ur.FK_ROLEID from tbl_userunit uu left join tbl_userrole ur on ur.FK_USERID = uu.FK_USERID');
        $olds = DB::connection('mysql_old')->select('select FK_UNITID, FK_USERID from tbl_userunit group by FK_UNITID, FK_USERID');
        $news = [];
        foreach ($olds as $old) {
            if (DB::table('role_user')->where(['user_id' => $old->FK_USERID, 'unit_id' => $old->FK_UNITID, 'role_id' => 15])->count() == 0) {
                $news[] = [
                    'user_id' => $old->FK_USERID,
                    'unit_id' => $old->FK_UNITID,
                    'role_id' => 15,    // Assistant Accountant
                    'is_default' => '0',
                    'created_at' => null,
                    'updated_at' => null
                ];
            }
        };
        DB::table('role_user')->insert($news);

        /*************************
         * Permission
         * -> Remake brand new permission system
         ************************* */
        $this->command->line("Permission (create new permission system)");
        Db::table('permissions')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'preference'],
            ['id' => 3, 'name' => 'vendor'],
            ['id' => 4, 'name' => 'purchase_admin'],
            ['id' => 5, 'name' => 'purchase_edit'],
            ['id' => 6, 'name' => 'voucher'],
            ['id' => 7, 'name' => 'order'],
        ]);

        /*************************
         * PermissionRole
         * -> Remake brand new permission system
         ************************* */
        $this->command->line("PermissionRole (create new permission system)");
        $roles = DB::table('roles')->get();
        $news = [];
        foreach ($roles as $role) {
            switch ($role->short_name) {
                case 'Admin':
                    $news[] = ['permission_id' => 1, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //admin
                    $news[] = ['permission_id' => 2, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //preference
                    $news[] = ['permission_id' => 3, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //vendor
                    $news[] = ['permission_id' => 6, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //voucher
                    $news[] = ['permission_id' => 7, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //order
                    break;
                case 'PS':
                case 'PO':
                case 'PA':
                    $news[] = ['permission_id' => 2, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //preference
                    $news[] = ['permission_id' => 3, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //vendor
                    $news[] = ['permission_id' => 4, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //purchase_admin
                    $news[] = ['permission_id' => 7, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //order
                    break;
                case 'Procurement specialist assistant':
                    $news[] = ['permission_id' => 2, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //preference
                    $news[] = ['permission_id' => 3, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //vendor
                    $news[] = ['permission_id' => 5, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //purchase_edit
                    $news[] = ['permission_id' => 7, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //order
                    break;
                case 'FO':
                    $news[] = ['permission_id' => 6, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //voucher
                    $news[] = ['permission_id' => 7, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //order
                    break;
                case 'Accountant':
                case 'Assistant accountant':
                case 'Cashier':
                    $news[] = ['permission_id' => 6, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //voucher
                    $news[] = ['permission_id' => 7, 'role_id' => $role->id, 'created_at' => null, 'updated_at' => null];  //order
                    break;
                }
        }
        DB::table('permission_role')->insert($news);

        /*************************
         * TrackUser
         * -> x(migrate from tbl_usercategory)
         * -> No need to migrate
         * -> Requisition search page can be searched by Units/Projects
         ************************* */
        // $this->command->line("TrackUser (Migrate from tbl_usercategory)");
        // $olds = DB::connection('mysql_old')->select('select * from tbl_usercategory');
        // $news = [];
        // foreach ($olds as $old) {
        //     $news[] = [
        //         'user_id' => $old->FK_USERID,
        //         'category' => $old->CATEGORY,
        //         'created_at' => null,
        //         'updated_at' => null
        //     ];
        // };
        // DB::table('track_users')->insert($news);


        /*************************
         * requisition_status
         * -> remake
         ************************* */
        $this->command->line("requisition_statuses");
        Db::table('requisition_statuses')->insert([
            //for procurement requisitions
            ['id' => 1,'module_id' => 1, 'old_requisition_status_id' => 1, 'name' => 'Requisition preparation'],
            ['id' => 2,'module_id' => 1, 'old_requisition_status_id' => 15, 'name' => 'Requisition checking'],
            ['id' => 3,'module_id' => 1, 'old_requisition_status_id' => 4, 'name' => 'Funds verification'],
            ['id' => 4,'module_id' => 1, 'old_requisition_status_id' => 2, 'name' => 'Requisition approval'],
            ['id' => 5,'module_id' => 1, 'old_requisition_status_id' => 3, 'name' => 'Quotation sourcing'],
            ['id' => 6,'module_id' => 1, 'old_requisition_status_id' => null, 'name' => 'Purchasing'],  //Add
            ['id' => 7,'module_id' => 1, 'old_requisition_status_id' => null, 'name' => 'Closed'],      //Add
            ['id' => 8,'module_id' => 1, 'old_requisition_status_id' => 16, 'name' => 'Canceled'],
            //for purchase_cheque
            ['id' => 10,'module_id' => 2, 'old_requisition_status_id' => 3, 'name' => 'purchase requisition generation'],
            ['id' => 11,'module_id' => 2, 'old_requisition_status_id' => 4, 'name' => 'Funds verification'],
            ['id' => 12,'module_id' => 2, 'old_requisition_status_id' => 13, 'name' => 'Purchase requisition approval'],
            ['id' => 13,'module_id' => 2, 'old_requisition_status_id' => 9, 'name' => 'Funds approval'],
            ['id' => 14,'module_id' => 2, 'old_requisition_status_id' => 12, 'name' => 'Sending Payment'],     //Add
            ['id' => 15,'module_id' => 2, 'old_requisition_status_id' => null, 'name' => 'Received/Rating'],     //Add
            ['id' => 16,'module_id' => 2, 'old_requisition_status_id' => null, 'name' => 'Closed'],     //Add
            ['id' => 17,'module_id' => 2, 'old_requisition_status_id' => 16, 'name' => 'Canceled'],
            //for purchase_lpo
            ['id' => 20,'module_id' => 3, 'old_requisition_status_id' => 3, 'name' => 'purchase requisition generation'],
            ['id' => 21,'module_id' => 3, 'old_requisition_status_id' => 4, 'name' => 'Funds verification'],
            ['id' => 22,'module_id' => 3, 'old_requisition_status_id' => 13, 'name' => 'Purchase requisition approval'],
            ['id' => 23,'module_id' => 3, 'old_requisition_status_id' => 9, 'name' => 'Funds approval'],
            ['id' => 24,'module_id' => 3, 'old_requisition_status_id' => 5, 'name' => 'LPO generation'],
            ['id' => 25,'module_id' => 3, 'old_requisition_status_id' => null, 'name' => 'Order processing'],   //Add
            ['id' => 26,'module_id' => 3, 'old_requisition_status_id' => null, 'name' => 'Received/Rating'],   //Add
            ['id' => 27,'module_id' => 3, 'old_requisition_status_id' => null, 'name' => 'Closed'],     //Add
            ['id' => 28,'module_id' => 3, 'old_requisition_status_id' => 16, 'name' => 'Canceled'],
            // for order
            ['id' => 40,'module_id' => 4, 'old_requisition_status_id' => null, 'name' => 'LPO generation'],
            ['id' => 41,'module_id' => 4, 'old_requisition_status_id' => 6, 'name' => 'LPO checking'],
            ['id' => 42,'module_id' => 4, 'old_requisition_status_id' => 14, 'name' => 'LPO Finalizing'],
            ['id' => 43,'module_id' => 4, 'old_requisition_status_id' => 8, 'name' => 'Goods/Items processing'],
            ['id' => 44,'module_id' => 4, 'old_requisition_status_id' => 12, 'name' => 'Payment processing'],
            ['id' => 45,'module_id' => 4, 'old_requisition_status_id' => null, 'name' => 'Closed'],     //Add
            ['id' => 46,'module_id' => 4, 'old_requisition_status_id' => null, 'name' => 'Canceled'],     //Add
            // for voucher
            ['id' => 50,'module_id' => 5, 'old_requisition_status_id' => 1, 'name' => 'preparing'],
            ['id' => 51,'module_id' => 5, 'old_requisition_status_id' => 2, 'name' => 'checking'],
            ['id' => 52,'module_id' => 5, 'old_requisition_status_id' => 3, 'name' => 'authorization'],
            ['id' => 53,'module_id' => 5, 'old_requisition_status_id' => 4, 'name' => 'cheque processing'],
            ['id' => 54,'module_id' => 5, 'old_requisition_status_id' => null, 'name' => 'Paid'],     //Add
            ['id' => 55,'module_id' => 5, 'old_requisition_status_id' => null, 'name' => 'Canceled'],     //Add
            // ??? not used?
            // ['id' => 60,'module_id' => 0, 'old_requisition_status_id' => 7, 'name' => 'Purchasing'],
            ['id' => 61,'module_id' => 0, 'old_requisition_status_id' => 10, 'name' => 'Receipt'],
            ['id' => 62,'module_id' => 0, 'old_requisition_status_id' => 11, 'name' => 'Verify items availability'],
        ]);

        /*************************
         * flows, flow_details
         ************************* */
        $this->command->line("flows, flow_details");

        $olds = DB::connection('mysql_old')->select('select * from tbl_flow f where FK_COMPANYID not in (5, 6, 7, 12) order by FK_COMPANYID, LEVEL, FK_ROUTEID');

        $moduleId = 1;
        $previousModuleId = null;
        $previousCompanyId = null;

        $qs = false;
        $lpo = false;
        $cheque = false;
        $forModule3 = [];

        $newFlow = [];
        $newFlowDetail = [];
        $newFlowDetailLevel = 1;

        foreach ($olds as $i => $old) {
            if ($old->FK_REQUISITIONSTATUS == 5) {
                //When LPO generation
                $lpo = true;
            }

            if ($previousCompanyId != $old->FK_COMPANYID) {
                $moduleId = config('const.MODULE.PROCUREMENT.PROCUREMENT');
                $qs = false;
                $lpo = false;
                $cheque = false;
                $forModule3 = [];
            } else if ($qs == false) {
                $moduleId = config('const.MODULE.PROCUREMENT.PROCUREMENT');
            } else if ($lpo == false) {
                $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE');
            } else {
                $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_LPO');
            }

            if ($previousCompanyId != $old->FK_COMPANYID || $previousModuleId != $moduleId) {
                $newFlow[] = [
                    'company_id' => $old->FK_COMPANYID,
                    'module_id' => $moduleId,
                    'created_at' => null,
                    'updated_at' => null
                ];
                $newFlowDetailLevel = 1;
            }

            $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'old_requisition_status_id' => $old->FK_REQUISITIONSTATUS])->first('id');
            if (empty($requisitionStatus)) {
                if (in_array($old->FK_REQUISITIONSTATUS, [6, 14, 8, 12]) !== false) {
                    //order
                } else {
                    //something wrong
                    dd($newFlow, $old);
                }
            } else {
                $newFlowDetail[] = [
                    'flow_id' => count($newFlow),
                    'level' => $newFlowDetailLevel++,
                    'role_id' => $old->FK_ROLEID,
                    'requisition_status_id' => $requisitionStatus->id,
                    'created_at' => null,
                    'updated_at' => null
                ];
                if ($moduleId == config('const.MODULE.PROCUREMENT.PROCUREMENT') && $old->FK_REQUISITIONSTATUS == 3) {
                    //When Quotation Sourcing

                    //Add new statuses
                    foreach (['Purchasing', 'Closed'] as $addStatus) {
                        $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
                        $newFlowDetail[] = [
                            'flow_id' => count($newFlow),
                            'level' => $newFlowDetailLevel++,
                            'role_id' => null,
                            'requisition_status_id' => $requisitionStatus->id,
                            'created_at' => null,
                            'updated_at' => null
                        ];
                    }

                    //Add Purchase requisition (cheque) flow
                    $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE');
                    $newFlow[] = [
                        'company_id' => $old->FK_COMPANYID,
                        'module_id' => $moduleId,
                        'created_at' => null,
                        'updated_at' => null
                    ];

                    $newFlowDetailLevel = 1;
                    $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'old_requisition_status_id' => $old->FK_REQUISITIONSTATUS])->first('id');
                    $newFlowDetail[] = [
                        'flow_id' => count($newFlow),
                        'level' => $newFlowDetailLevel++,
                        'role_id' => $old->FK_ROLEID,
                        'requisition_status_id' => $requisitionStatus->id,
                        'created_at' => null,
                        'updated_at' => null
                    ];
                    $qs = true;

                } else if ($moduleId == config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE') && $old->FK_REQUISITIONSTATUS == 12) {
                    // When last of "purchase requisition (cheque)" flow

                    // Replace last Registrar's requisition status 'Funds verification' to 'Sending Payment'
                    array_pop($newFlowDetail);
                    $newFlowDetail[count($newFlowDetail) - 1]['requisition_status_id'] = $requisitionStatus->id;
                    $newFlowDetailLevel--;

                    //Add new statuses
                    foreach (['Received/Rating', 'Closed'] as $addStatus) {
                        $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
                        $newFlowDetail[] = [
                            'flow_id' => count($newFlow),
                            'level' => $newFlowDetailLevel++,
                            'role_id' => ($addStatus == 'Received/Rating' ? 2 : null),   //2: member
                            'requisition_status_id' => $requisitionStatus->id,
                            'created_at' => null,
                            'updated_at' => null
                        ];
                    }

                    //Add Purchase requisition (LPO) flow
                    $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_LPO');
                    $newFlow[] = [
                        'company_id' => $old->FK_COMPANYID,
                        'module_id' => $moduleId,
                        'created_at' => null,
                        'updated_at' => null
                    ];

                    $newFlowDetailLevel = 1;
                    foreach ($forModule3 as $module3Status) {
                        $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'old_requisition_status_id' => $module3Status['old_requisition_status_id']])->first('id');
                        $newFlowDetail[] = [
                            'flow_id' => count($newFlow),
                            'level' => $newFlowDetailLevel++,
                            'role_id' => $module3Status['role_id'],
                            'requisition_status_id' => $requisitionStatus->id,
                            'created_at' => null,
                            'updated_at' => null
                        ];
                    }
                    $cheque = true;


                } else if ($moduleId == config('const.MODULE.PROCUREMENT.PURCHASE_LPO') && $old->FK_REQUISITIONSTATUS == 5) {
                    // when LPO GENERATION

                    // **No order system for temporary**

                    // //Add new statuses
                    // foreach (['Order processing'] as $addStatus) {
                    //     $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
                    //     $newFlowDetail[] = [
                    //         'flow_id' => count($newFlow),
                    //         'level' => $newFlowDetailLevel++,
                    //         'role_id' => null,
                    //         'requisition_status_id' => $requisitionStatus->id,
                    //         'created_at' => null,
                    //         'updated_at' => null
                    //     ];
                    // }

                // } else if ($moduleId == config('const.MODULE.PROCUREMENT.PURCHASE_LPO') && $old->FK_REQUISITIONSTATUS == 8) {
                //     // When last of "purchase requisition (LPO)" flow

                    //Add new statuses
                    foreach (['Received/Rating', 'Closed'] as $addStatus) {
                        $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
                        $newFlowDetail[] = [
                            'flow_id' => count($newFlow),
                            'level' => $newFlowDetailLevel++,
                            'role_id' => ($addStatus == 'Received/Rating' ? 2 : null),   //2: member
                            'requisition_status_id' => $requisitionStatus->id,
                            'created_at' => null,
                            'updated_at' => null
                        ];
                    }
                }

                if ($qs == true && $cheque == false) {
                    $forModule3[] = ['role_id' => $old->FK_ROLEID, 'old_requisition_status_id' => $old->FK_REQUISITIONSTATUS];
                }
            }

            $previousModuleId = $moduleId;
            $previousCompanyId = $old->FK_COMPANYID;
        };

        //Order
        $moduleId = config('const.MODULE.PROCUREMENT.ORDER');
        $newFlow[] = [
            'company_id' => null, // shared flow
            'module_id' => $moduleId,
            'created_at' => null,
            'updated_at' => null
        ];
        $newOrderStatuses = Db::table('requisition_statuses')->where(['module_id' => $moduleId])->get();
        $oldOrderFlows = DB::connection('mysql_old')->select('select * from tbl_flow where FK_COMPANYID = 1 and FK_REQUISITIONSTATUS in (6, 14, 8, 12) order by LEVEL');
        foreach ($oldOrderFlows as $i => $oldOrderFlow) {
            $newFlowDetail[] = [
                'flow_id' => count($newFlow),
                'level' => ($i + 1),
                'role_id' => $oldOrderFlow->FK_ROLEID,
                'requisition_status_id' => $newOrderStatuses[$i]->id,
                'created_at' => null,
                'updated_at' => null
            ];
        }
        //Add new statuses
        foreach (['Closed', 'Canceled'] as $j => $addStatus) {
            $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
            $newFlowDetail[] = [
                'flow_id' => count($newFlow),
                'level' => $i + $j + 2,
                'role_id' => null,
                'requisition_status_id' => $requisitionStatus->id,
                'created_at' => null,
                'updated_at' => null
            ];
        }

        //Voucher
        $moduleId = config('const.MODULE.PROCUREMENT.VOUCHER');
        $newFlow[] = [
            'company_id' => null, // shared flow
            'module_id' => $moduleId,
            'created_at' => null,
            'updated_at' => null
        ];
        $newVoucherStatuses = Db::table('requisition_statuses')->where(['module_id' => $moduleId])->get();
        $oldVoucherFlows = DB::connection('mysql_old')->select('select * from tbl_vflow');
        foreach ($oldVoucherFlows as $i => $oldVoucherFlow) {
            $newFlowDetail[] = [
                'flow_id' => count($newFlow),
                'level' => $oldVoucherFlow->LEVEL,
                'role_id' => $oldVoucherFlow->FK_ROLEID,
                'requisition_status_id' => $newVoucherStatuses[$i]->id,
                'created_at' => null,
                'updated_at' => null
            ];
        }
        //Add new statuses
        foreach (['Paid', 'Canceled'] as $j => $addStatus) {
            $requisitionStatus = Db::table('requisition_statuses')->where(['module_id' => $moduleId, 'name' => $addStatus])->first('id');
            $newFlowDetail[] = [
                'flow_id' => count($newFlow),
                'level' => $i + $j + 2,
                'role_id' => null,
                'requisition_status_id' => $requisitionStatus->id,
                'created_at' => null,
                'updated_at' => null
            ];
        }

        DB::table('flows')->insert($newFlow);
        DB::table('flow_details')->insert($newFlowDetail);


        /*************************
         * items
         ************************* */
        $this->command->line("items");
        $olds = DB::connection('mysql_old')->select('select * from tbl_item');
        $items = [];
        $replaceItemNo = [];
        $news = [];
        foreach ($olds as $old) {
            $searchItemName = strtoupper(trim(str_replace( "\xc2\xa0", " ", $old->NAME)));
            if(isset($items[$searchItemName])) {
                $replaceItemNo[$old->PK_ITEMID] = $items[$searchItemName];
            } else {
                $items[$searchItemName] = $old->PK_ITEMID;
                $news[] = [
                    'id' => $old->PK_ITEMID,
                    'name' => trim($old->NAME),
                    'code' => $old->CODE,
                    'created_at' => null,
                    'updated_at' => null
                ];
            }
            if ($i % 1000 == 0) {
                DB::table('items')->insert($news);
                $news = [];
            }
        }
        DB::table('items')->insert($news);


        /*************************
         * suppliers
         * -> delete duplicated suppliers (if name, address, phone and email are duplicated)
         ************************* */
        $this->command->line("suppliers");
        $olds = DB::connection('mysql_old')->select('select s.*, d.PK_SUPPLIERID as DUPLICATED from tbl_supplier s left join tbl_supplier d on d.SUPPLIER_NAME = s.SUPPLIER_NAME and
        d.ADDRESS = s.ADDRESS and d.PHONE = s.PHONE and d.SUPPLIER_EMAILADDRESS = s.SUPPLIER_EMAILADDRESS and d.PK_SUPPLIERID != s.PK_SUPPLIERID order by s.PK_SUPPLIERID');
        $news = [];
        $suppliers = [];
        $duplicateSuppliers = [
            310 => 20,
            484 => 189,
            81 => 191,
            613 => 612,
            184 => 172,
            264 => 172,
            484 => 483,
            532 => 396,
            97 => 13,
            552 => 553,
            443 => 377,
            444 => 508,
            637 => 511,
            636 => 575,
            155 => 63,
            152 => 145,
            134 => 102,
            445 => 282,
            238 => 237,
            50 => 49,
            166 => 105,
            561 => 618,
            338 => 62,
            665 => 662,
            449 => 393
        ];
        foreach ($olds as $old) {
            if (!in_array($old->PK_SUPPLIERID, $suppliers) && empty($duplicateSuppliers[$old->PK_SUPPLIERID])) {
                $news[] = [
                    'id' => $old->PK_SUPPLIERID,
                    'name' => trim($old->SUPPLIER_NAME),
                    'address' => trim($old->ADDRESS) ?? '',
                    // 'phone' => empty($old->PHONE) ? null : json_encode([$old->PHONE]),
                    // 'email' => empty($old->SUPPLIER_EMAILADDRESS) ? null : json_encode([$old->SUPPLIER_EMAILADDRESS]),
                    'phone' => trim($old->PHONE),
                    'email' => trim($old->SUPPLIER_EMAILADDRESS),
                    'created_at' => null,
                    'updated_at' => null
                ];
                $suppliers[] = $old->PK_SUPPLIERID;
                if (!empty($old->DUPLICATED)) {
                    $duplicateSuppliers[$old->DUPLICATED] = $old->PK_SUPPLIERID;
                }
            } else if (empty($duplicateSuppliers[$old->PK_SUPPLIERID]) && !empty($old->DUPLICATED)) {
                $duplicateSuppliers[$old->DUPLICATED] = $old->PK_SUPPLIERID;
            }
        };
        DB::table('suppliers')->insert($news);

        /*************************
         * procurements, trails
         ************************* */
        $this->command->line("procurements, trails(for procurements)");
        $flows = Db::table('units')->leftJoin('flows', 'flows.company_id', '=', 'units.company_id')->select('units.id as unit_id', 'flows.id as flow_id')->where('module_id', config('const.MODULE.PROCUREMENT.PROCUREMENT'))->orderBy('units.id')->pluck('flow_id', 'unit_id');
        $flowsPurchase = Db::table('units')->leftJoin('flows', 'flows.company_id', '=', 'units.company_id')->select('units.id as unit_id', 'flows.id as flow_id')->where('module_id', config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE'))->orderBy('units.id')->pluck('flow_id', 'unit_id');

        $olds = DB::connection('mysql_old')->select('select * from tbl_requisition');
        foreach ($olds as $old) {
            $trails = [];

            $oldRequisitionTransactions = DB::connection('mysql_old')->select('select * from tbl_requisitiontransaction rt left join tbl_flow f on f.PK_FLOW = rt.FK_FLOWID where FK_REQUISITIONID = ' . $old->PK_REQUISITIONID . ' order by rt.TRANSACTIONDATE asc');
            foreach ($oldRequisitionTransactions as $i => $oldRequisitionTransaction) {

                $flowId = $flows[$old->FK_UNITID];
                $level = $oldRequisitionTransaction->LEVEL ?? 1;
                $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['level', $level]])->first();
                // if (empty($flowDetail)) dd('procurement-trail', $flowId, $level, $flowDetail, $oldRequisitionTransaction);

                $nextTransaction = empty($oldRequisitionTransactions[$i + 1]) ? null : $oldRequisitionTransactions[$i + 1];
                if (!empty($nextTransaction)) {
                    $trails[] = [
                        'trailable_id' => $old->PK_REQUISITIONID,
                        'trailable_type' => 'procurements',
                        'flow_id' => $flowId,
                        'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                        // 'level' => $level,
                        // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
                        'user_id' => $oldRequisitionTransaction->FK_USERID,
                        'status' => $nextTransaction->STATUS,
                        'comment' => $nextTransaction->COMMENT ?? '',
                        'transaction_at' => !empty($nextTransaction) ? $nextTransaction->TRANSACTIONDATE : null,
                        'created_at' => $oldRequisitionTransaction->TRANSACTIONDATE,
                        'updated_at' => null,
                    ];
                } else {
                    $trails[] = [
                        'trailable_id' => $old->PK_REQUISITIONID,
                        'trailable_type' => 'procurements',
                        'flow_id' => $flowId,
                        'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                        // 'level' => $level,
                        // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
                        'user_id' => $oldRequisitionTransaction->FK_USERID,
                        'status' => 'CHECKING',
                        'comment' => null,
                        'transaction_at' => null,
                        'created_at' => $oldRequisitionTransaction->TRANSACTIONDATE,
                        'updated_at' => null
                    ];
                }

            }
            if (!empty($old->CURRENT_USERID) && (empty($oldRequisitionTransactions) || ($oldRequisitionTransaction->FK_USERID != $old->CURRENT_USERID && $trails[count($trails) - 1]['status'] !== 'CHECKING'))) {
                // Add trail record for current user
                $flowId = $flows[$old->FK_UNITID];
                $level = 1;
                if (!empty($oldRequisitionTransactions)) {
                    $level = $oldRequisitionTransaction->LEVEL + 1;
                }
                $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['level', $level]])->first();
                // if (empty($flowDetail)) dd('procurement-add-trail', $flowId, $level, $flowDetail, $oldRequisitionTransactions);

                $trails[] = [
                    'trailable_id' => $old->PK_REQUISITIONID,
                    'trailable_type' => 'procurements',
                    'flow_id' => $flowId,
                    'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                    // 'level' => $level,
                    // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
                    'user_id' => $old->CURRENT_USERID,
                    'status' => 'CHECKING',
                    'comment' => null,
                    'transaction_at' => null,
                    'created_at' => empty($oldRequisitionTransactions) ? $old->DATECREATED : $trails[count($trails) - 1]['transaction_at'],
                    'updated_at' => null
                ];
            }

            DB::table('trails')->insert($trails);

            $requisitionStatusId = 1;
            if (!empty($old->FK_REQUISITIONSTATUS)) {
                $lastTrail = DB::table('trails')->where([['trailable_id', $old->PK_REQUISITIONID], ['trailable_type', 'procurements']])->orderBy('id', 'desc')->first();
                if (!empty($lastTrail)) {
                    // $currentStatus = DB::table('flow_details')->where([['flow_id', $lastTrail->flow_id], ['level', $lastTrail->level]])->first('requisition_status_id');
                    $currentStatus = DB::table('flow_details')->find($lastTrail->flow_detail_id);
                    if (!empty($currentStatus)) {
                        $requisitionStatusId = $currentStatus->requisition_status_id;
                    }
                }
            }
            $procurement = [
                'id' => $old->PK_REQUISITIONID,
                'title' => $old->TITLE,
                'unit_id' => $old->FK_UNITID,
                // 'trail_id' => DB::table('trails')->max('id'),   //latest trail id
                'requisition_status_id' => $requisitionStatusId,
                'current_user_id' => $old->CURRENT_USERID,
                'created_user_id' => $old->FK_USERID,
                'created_at' => $old->DATECREATED,
                'updated_at' => null
            ];
            DB::table('procurements')->insert($procurement);
        }


        /*************************
         * purchases, trails
         ************************* */
        $this->command->line("purchases, trails(for purchases)");
        $flowsCheque = Db::table('units')->leftJoin('flows', 'flows.company_id', '=', 'units.company_id')->select('units.id as unit_id', 'flows.id as flow_id')->where('module_id', config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE'))->orderBy('units.id')->pluck('flow_id', 'unit_id');
        $flowsLPO = Db::table('units')->leftJoin('flows', 'flows.company_id', '=', 'units.company_id')->select('units.id as unit_id', 'flows.id as flow_id')->where('module_id', config('const.MODULE.PROCUREMENT.PURCHASE_LPO'))->orderBy('units.id')->pluck('flow_id', 'unit_id');

        $olds = DB::connection('mysql_old')->select('select pr.*, r.FK_UNITID, r.FK_USERID as REQUISITION_CREATED_USER_ID from tbl_pr pr left join tbl_requisition r on r.PK_REQUISITIONID = pr.FK_REQUISITIONID');
        foreach ($olds as $old) {
            $trails = [];
            $oldPurchaseTransactions = DB::connection('mysql_old')->select('select * from tbl_purchasetransaction pt left join tbl_flow f on f.PK_FLOW = pt.FK_FLOWID where FK_PRID = ' . $old->PK_PRID . ' order by pt.TRANSACTIONDATE asc');
            $flowId = $old->FK_ROUTEID == 1 ? $flowsCheque[$old->FK_UNITID] : $flowsLPO[$old->FK_UNITID];

            // add quotationSource trail
            $quotationSource = DB::table('trails')->leftJoin('flow_details', 'flow_details.id', '=', 'trails.flow_detail_id')
                ->where([
                    'trailable_type' => 'procurements',
                    'requisition_status_id' => config('const.REQUISITION_STATUS.PROCUREMENT.QUOTATION_SOURCING'),
                    'trailable_id' => $old->FK_REQUISITIONID
                ])->get();

            $purchaseLevel = 1;
            if (!$quotationSource->isEmpty()) {
                $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['level', $purchaseLevel]])->first();
                $trails[] = [
                    'trailable_id' => $old->PK_PRID,
                    'trailable_type' => 'purchases',
                    'flow_id' => $flowId,
                    'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                    // 'level' => $purchaseLevel,
                    'user_id' => $quotationSource[0]->user_id,
                    'status' => !empty($oldPurchaseTransactions) ? 'NORMAL' : 'CHECKING',
                    'comment' => !empty($oldPurchaseTransactions) ? $oldPurchaseTransactions[0]->COMMENT : '',
                    'transaction_at' => !empty($oldPurchaseTransactions) ? $oldPurchaseTransactions[0]->TRANSACTIONDATE : null,
                    'created_at' => $quotationSource[0]->created_at,
                    'updated_at' => null,
                ];
            }

            foreach ($oldPurchaseTransactions as $i => $oldPurchaseTransaction) {
                if (in_array($oldPurchaseTransaction->FK_REQUISITIONSTATUS, [6, 14, 12]) === false) {
                    // 6, 14 -> orders に移行   12 -> orders/voucher に移行

                    $purchaseLevel = ($oldPurchaseTransaction->STATUS == 'RETURNED') ? $purchaseLevel - 1 : $purchaseLevel + 1;
                    if ($purchaseLevel == 2 && date($oldPurchaseTransaction->TRANSACTIONDATE) <= date('2019-09-20')) {
                        // 2019/9/20 PI check implemented
                        if ($oldPurchaseTransaction->STATUS == 'RETURNED') {
                            $purchaseLevel--;
                        } else {
                            $purchaseLevel++;
                        }
                    }
                    $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['level', $purchaseLevel]])->first();



                    $userBelongsToUnit = DB::table('role_user')->where([['user_id', $oldPurchaseTransaction->FK_USERID], ['unit_id', $old->FK_UNITID]])->count();
                    $userUnitId = null;
                    if ($userBelongsToUnit > 0) {
                        $userUnitId = $old->FK_UNITID;
                    } else {
                        $userUnit = DB::table('role_user')->where('user_id', $oldPurchaseTransaction->FK_USERID)->first();
                        $userUnitId = !empty($userUnit) ? $userUnit->unit_id : null;
                    }

                    $nextTransaction = empty($oldPurchaseTransactions[$i + 1]) ? null : $oldPurchaseTransactions[$i + 1];
                    if (!empty($nextTransaction)) {
                        $trails[] = [
                            'trailable_id' => $old->PK_PRID,
                            'trailable_type' => 'purchases',
                            'flow_id' => $flowId,
                            'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                            // 'level' => $purchaseLevel,
                            'user_id' => $oldPurchaseTransaction->FK_USERID,
                            'status' => $nextTransaction->STATUS,
                            'comment' => $nextTransaction->COMMENT ?? '',
                            'transaction_at' => $nextTransaction->TRANSACTIONDATE,
                            'created_at' => $oldPurchaseTransaction->TRANSACTIONDATE,
                            'updated_at' => null,
                        ];
                    } else {
                        $trails[] = [
                            'trailable_id' => $old->PK_PRID,
                            'trailable_type' => 'purchases',
                            'flow_id' => $flowId,
                            'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                            // 'level' => $purchaseLevel,
                            'user_id' => $oldPurchaseTransaction->FK_USERID,
                            'status' => 'CHECKING',
                            'comment' => '',
                            'transaction_at' => null,
                            'created_at' => $oldPurchaseTransaction->TRANSACTIONDATE,
                            'updated_at' => null,
                        ];
                    }

                } else if ($oldPurchaseTransaction->FK_REQUISITIONSTATUS == 12 && $old->FK_ROUTEID == 1) {
                    // Add "Rating" flow if status = "payment processing" & route = cheque
                    //
                    // $purchaseLevel++;
                    $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['requisition_status_id', config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.RECEIVED_RATING')]])->first();

                    $trails[] = [
                        'trailable_id' => $old->PK_PRID,
                        'trailable_type' => 'purchases',
                        'flow_id' => $flowId,
                        'flow_detail_id' => empty($flowDetail) ? null : $flowDetail->id,
                        // 'level' => $purchaseLevel,
                        'user_id' => $old->REQUISITION_CREATED_USER_ID,
                        'status' => 'CHECKING',
                        'comment' => '',
                        'transaction_at' => null,
                        'created_at' => $oldPurchaseTransaction->TRANSACTIONDATE,
                        'updated_at' => null,
                    ];
                }
            }

            DB::table('trails')->insert($trails);


            $requisitionStatusId = $old->FK_ROUTEID == 1 ? 10 : 20;
            if (!empty($old->FK_REQUISITIONSTATUS)) {
                $lastTrail = DB::table('trails')->where([['trailable_id', $old->PK_PRID], ['trailable_type', 'purchases']])->orderBy('id', 'desc')->first();
                if (!empty($lastTrail)) {
                    // $currentStatus = DB::table('flow_details')->where([['flow_id', $lastTrail->flow_id], ['level', $lastTrail->level]])->first('requisition_status_id');
                    $currentStatus = DB::table('flow_details')->find($lastTrail->flow_detail_id);
                    if (!empty($currentStatus)) {
                        $requisitionStatusId = $currentStatus->requisition_status_id;
                    }
                }
            }
            $purchase = [
                'id' => $old->PK_PRID,
                'procurement_id' => $old->FK_REQUISITIONID,
                'supplier_id' => !empty($duplicateSuppliers[$old->FK_SUPPLIERID]) ? $duplicateSuppliers[$old->FK_SUPPLIERID] : $old->FK_SUPPLIERID, //integrate duplicated supplier
                'route' => $old->FK_ROUTEID == 1 ? 'CHEQUE' : 'LPO',
                // 'trail_id' => DB::table('trails')->max('id'),   //latest trail id
                'requisition_status_id' => $requisitionStatusId,
                'current_user_id' => !empty($trails) ? $trails[count($trails) - 1]['user_id'] : $old->PR_CURRENT_USERID,
                'created_user_id' => $old->FK_USERID,
                'created_at' => $old->DATECREATED,
                'updated_at' => null
            ];
            DB::table('purchases')->insert($purchase);
        }


        /*************************
         * procurement_items
         ************************* */
        $this->command->line("procurement_items");
        $olds = DB::connection('mysql_old')->select('select * from tbl_requisitionitem');
        $news = [];
        foreach ($olds as $i => $old) {
           $news[] = [
                'id' => $old->PK_REQUISITIONITEMID,
                'procurement_id' => $old->FK_REQUISITIONID,
                'item_id' => isset($replaceItemNo[$old->FK_ITEMID]) ? $replaceItemNo[$old->FK_ITEMID] : $old->FK_ITEMID,
                'quantity' => $old->QUANTITY,
                'uom' => $old->UOM,
                'description' => $old->ITEMDESCRIPTION,
                'purchase_id' => $old->FK_PRID,
                'amount' => $old->AMOUNT,
                'received' => 0,
                'created_at' => null,
                'updated_at' => null
            ];

            if ($i % 1000 == 0) {
                DB::table('procurement_items')->insert($news);
                $news = [];
            }
        }
        DB::table('procurement_items')->insert($news);

        // change procurement status if all items are all purchased
        $procurementIds = Db::table('procurements')->pluck('id')->all();
        foreach ($procurementIds as $procurementId) {
            $notPurchasedCount = DB::table('procurement_items')->where('procurement_id', $procurementId)->whereNull('purchase_id')->count();
            if ($notPurchasedCount == 0) {
                // check all purchase are not "purchase requisition generation" status
                $purchaseIds = DB::table('procurement_items')->where('procurement_id', $procurementId)->groupBy('purchase_id')->pluck('purchase_id')->all();
                $generatingPurchaseCount = DB::table('purchases')->whereIn('id', $purchaseIds)->whereIn('requisition_status_id', [10, 20])->count();
                if ($generatingPurchaseCount == 0) {
                    // update status "purchasing"
                    Db::table('procurements')->where('id', $procurementId)->update([
                        'requisition_status_id' => config('const.REQUISITION_STATUS.PROCUREMENT.PURCHASING'),
                        'current_user_id' => null
                    ]);

                    $procurement = Db::table('procurements')->where('id', $procurementId)->first();
                    $flowId = $flows[$procurement->unit_id];
                    $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['requisition_status_id', config('const.REQUISITION_STATUS.PROCUREMENT.PURCHASING')]])->first();
                    $maxCreatedAt = Db::table('purchases')->select(DB::raw('max(created_at) as max_created_at'))->where('procurement_id', $procurementId)->first();

                    // update status of last trail
                    Db::table('trails')->where([['trailable_type', 'procurements'], ['trailable_id', $procurementId], ['status', 'CHECKING']])->update([
                        'status' => 'NORMAL',
                        'transaction_at' => $maxCreatedAt->max_created_at
                    ]);

                    // insert new trail
                    Db::table('trails')->insert([
                        [
                            'trailable_id' => $procurementId,
                            'trailable_type' => 'procurements',
                            'flow_id' => $flowId,
                            'flow_detail_id' => $flowDetail->id,
                            // 'level' => $level,
                            // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
                            'user_id' => null,
                            'status' => 'CHECKING',
                            'comment' => null,
                            'transaction_at' => null,
                            'created_at' => Carbon::parse($maxCreatedAt->max_created_at)->format('Y-m-d') . ' 23:59:59',
                            'updated_at' => null
                        ]
                    ]);
                }
            }
        }

        /*************************
         * orders, trails
         ************************* */
        $this->command->line("orders, trails(for orders)");
        $flowId = Db::table('flows')->where('flows.module_id', config('const.MODULE.PROCUREMENT.ORDER'))->first('id')->id;
        $flowDetails = Db::table('flow_details')->where('flow_id', $flowId)->pluck('id', 'level')->all();
        $olds = DB::connection('mysql_old')->select('select * from tbl_order');

        foreach ($olds as $old) {

            $trails = [];
            $oldOrderTransactions = DB::connection('mysql_old')->select('select * from tbl_purchasetransaction pt left join tbl_pr pr on pr.PK_PRID = pt.FK_PRID left join tbl_flow f on f.PK_FLOW = pt.FK_FLOWID where pr.FK_ROUTEID = 2 and f.FK_REQUISITIONSTATUS in (6, 14, 12) and pt.STATUS = "NORMAL" and pr.FK_ROUTEID = f.FK_ROUTEID and pt.FK_PRID = ' . $old->FK_PRID . ' order by pt.TRANSACTIONDATE asc ');
            $previousTransactionDate = $old->SAVED_DATE;
            foreach ($oldOrderTransactions as $i => $oldOrderTransaction) {
                $trails[] = [
                    'trailable_id' => $old->PK_OID,
                    'trailable_type' => 'orders',
                    'flow_id' => $flowId,
                    'flow_detail_id' => $flowDetails[$i + 1],
                    // 'level' => ($i + 1),
                    'user_id' => $oldOrderTransaction->FK_USERID,
                    'status' => $oldOrderTransaction->STATUS,
                    'comment' => $oldOrderTransaction->COMMENT ?? '',
                    'transaction_at' => $oldOrderTransaction->TRANSACTIONDATE,
                    'created_at' => $previousTransactionDate,
                    'updated_at' => null,
                ];
                $previousTransactionDate = $oldOrderTransaction->TRANSACTIONDATE;
            }
            // Add trail record for current user
            $trails[] = [
                'trailable_id' => $old->PK_OID,
                'trailable_type' => 'orders',
                'flow_id' => $flowId,
                'flow_detail_id' => $flowDetails[count($trails) + 1],
                // 'level' => count($trails) + 1,
                'user_id' => $old->RECEIVEDBY,
                'status' => 'CHECKING',
                'comment' => null,
                'transaction_at' => null,
                'created_at' => $previousTransactionDate,
                'updated_at' => null
            ];
            DB::table('trails')->insert($trails);

            $order = [
                'id' => $old->PK_OID,
                'purchase_id' => $old->FK_PRID,
                'po_number' => $old->PONUMBER,
                // 'trail_id' => DB::table('trails')->max('id'),   //latest trail id
                'requisition_status_id' => 40 + count($trails) - 1,
                'current_user_id' => $old->RECEIVEDBY,
                'created_user_id' => null,
                'signature' => $old->SIGNATURE,
                'created_at' => Carbon::parse($old->SAVED_DATE)->addHour(12)->addMinutes(15)->format('Y-m-d H:i:s'),
                'updated_at' => null
            ];
            DB::table('orders')->insert($order);
        }

        // change purchase status if already ordered
        $orders = Db::table('orders')->get();
        foreach ($orders as $order) {
            // // update status "Order processing"
            // Db::table('purchases')->where('id', $order->purchase_id)->update([
            //     'requisition_status_id' => config('const.REQUISITION_STATUS.PURCHASE_LPO.ORDER_PROCESSING'),
            //     'current_user_id' => null
            // ]);
            // // update status of last trail
            // Db::table('trails')->where([['trailable_type', 'purchases'], ['trailable_id', $order->purchase_id], ['status', 'CHECKING']])->update([
            //     'status' => 'NORMAL',
            //     'transaction_at' => $order->created_at
            // ]);
            // // insert new trail
            // $purchase = DB::table('purchases')->where('id', $order->purchase_id)->first();
            // $procurement = DB::table('procurements')->where('id', $purchase->procurement_id)->first();
            // $flowId = $flowsLPO[$procurement->unit_id];
            // $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['requisition_status_id', config('const.REQUISITION_STATUS.PURCHASE_LPO.ORDER_PROCESSING')]])->first();

            // Db::table('trails')->insert([
            //     [
            //         'trailable_id' => $order->purchase_id,
            //         'trailable_type' => 'purchases',
            //         'flow_id' => $flowId,
            //         'flow_detail_id' => $flowDetail->id,
            //         // 'level' => $level,
            //         // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
            //         'user_id' => null,
            //         'status' => 'CHECKING',
            //         'comment' => null,
            //         'transaction_at' => null,
            //         'created_at' => $order->created_at,
            //         'updated_at' => null
            //     ]
            // ]);

            $purchase = DB::table('purchases')->where('id', $order->purchase_id)->first();
            $procurement = DB::table('procurements')->where('id', $purchase->procurement_id)->first();

            // update status "Rate/Rating"
            Db::table('purchases')->where('id', $order->purchase_id)->update([
                'requisition_status_id' => config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING'),
                'current_user_id' => $procurement->created_user_id
            ]);
            // update status of last trail
            Db::table('trails')->where([['trailable_type', 'purchases'], ['trailable_id', $order->purchase_id], ['status', 'CHECKING']])->update([
                'status' => 'NORMAL',
                'transaction_at' => $order->created_at
            ]);
            // insert new trail
            $flowId = $flowsLPO[$procurement->unit_id];
            $flowDetail = Db::table('flow_details')->where([['flow_id', $flowId], ['requisition_status_id', config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING')]])->first();

            Db::table('trails')->insert([
                [
                    'trailable_id' => $order->purchase_id,
                    'trailable_type' => 'purchases',
                    'flow_id' => $flowId,
                    'flow_detail_id' => $flowDetail->id,
                    // 'level' => $level,
                    // 'requisition_status_id' => empty($flowDetail) ? 0 : $flowDetail->requisition_status_id,
                    'user_id' => $procurement->created_user_id,
                    'status' => 'CHECKING',
                    'comment' => null,
                    'transaction_at' => null,
                    'created_at' => $order->created_at,
                    'updated_at' => null
                ]
            ]);
        }


        /*************************
         * Voucher, trails
         ************************* */
        $this->command->line("vouchers, trails(for vouchers)");
        $flowId = Db::table('flows')->where('flows.module_id', config('const.MODULE.PROCUREMENT.VOUCHER'))->first('id')->id;
        $flowDetails = Db::table('flow_details')->where('flow_id', $flowId)->pluck('id', 'level')->all();
        $olds = DB::connection('mysql_old')->select('select * from tbl_vourcher');
        foreach ($olds as $old) {

            $trails = [];
            $oldVoucherTransactions = DB::connection('mysql_old')->select('select * from tbl_vtransaction rt INNER join tbl_vflow vf on vf.PK_VFID = rt.FK_VFID where FK_VID = ' . $old->PK_PVID);
            $previousTransactionDate = Carbon::parse($old->AUTO_CREATED_ON)->format('Y-m-d') . ' 00:00:00';
            foreach ($oldVoucherTransactions as $i => $oldVoucherTransaction) {
                $trails[] = [
                    'trailable_id' => $old->PK_PVID,
                    'trailable_type' => 'vouchers',
                    'flow_id' => $flowId,
                    'flow_detail_id' => $flowDetails[$oldVoucherTransaction->LEVEL],
                    // 'level' => $oldVoucherTransaction->LEVEL,
                    'user_id' => $oldVoucherTransaction->FK_USERID,
                    'status' => (empty($oldVoucherTransaction->TDATE) ? 'CHECKING' : 'NORMAL'),
                    'comment' => '',
                    'transaction_at' => $oldVoucherTransaction->TDATE,
                    'created_at' => $previousTransactionDate,
                    'updated_at' => null,
                ];
                $previousTransactionDate = $oldVoucherTransaction->TDATE;
            }
            if (empty($trails)) {
                // Add trail record for current user
                $trails[] = [
                    'trailable_id' => $old->PK_PVID,
                    'trailable_type' => 'vouchers',
                    'flow_id' => $flowId,
                    'flow_detail_id' => $flowDetails[1],
                    // 'level' => 1,
                    'user_id' => $old->RECEIVED_BY,
                    'status' => 'CHECKING',
                    'comment' => null,
                    'transaction_at' => null,
                    'created_at' => $previousTransactionDate,
                    'updated_at' => null
                ];
            }
            DB::table('trails')->insert($trails);

            $currentVoucherTrail = DB::table('trails')->latest('id')->first();

            //calc total_amount
            $total = $old->TOTAL_AMOUNT;
            if (empty($total)) {
                $purchaseItems = DB::table('procurement_items')->where('purchase_id', $old->REQID)->get();
                $total = 0;
                foreach ($purchaseItems as $purchaseItem) {
                    $total += $purchaseItem->quantity * $purchaseItem->amount;
                }
            }
            $voucher = [
                'id' => $old->PK_PVID,
                'purchase_id' => $old->REQID,
                'expenditure_code' => $old->EXPENDITURE_CODE,
                'excepted_tax' => $old->EXCEPTED_TAX,
                'withholding_tax_code' => $old->WITHHOLDING_TAX_CODE,
                'tax_applied' => $old->TAXAPPLIED,
                'total_amount' => $total,
                // 'trail_id' => $currentVoucherTrail->id,   //latest trail id
                'requisition_status_id' => 50 + count($trails) - 1,
                'assigned_accountant_user_id' => $old->RECEIVED_BY,
                'current_user_id' => $currentVoucherTrail->user_id, // latest trail user
                'created_user_id' => null,
                'created_at' => $old->AUTO_CREATED_ON,
                'updated_at' => null
            ];
            DB::table('vouchers')->insert($voucher);
        }


        /*************************
         * documents
         ************************* */
        //tbl_document
        $this->command->line("documents (from tbl_document)");
        $olds = DB::connection('mysql_old')->select('select * from tbl_document');
        $news = [];
        foreach ($olds as $i => $old) {
            $documentType = '';
            switch ($old->FK_DCATID) {
                case 1: $documentType ='Invoice'; break;
                case 2: $documentType ='Quotation'; break;
                case 3: $documentType ='Misc'; break;
            }
            $fileNames = explode('.', $old->FILE_PATH);
            $extension = end($fileNames);
            $news[] = [
                'documentable_id' => $old->FK_REQUISITIONID,
                'documentable_type' => 'procurements',
                'document_type' => $documentType,
                'file_path' => 'uploads/documents/' . $old->FILE_PATH,
                'file_name' => $old->FILE_PATH,
                'file_extension' => $extension,
                'checked' => '0',
                'created_user_id' => null,
                'created_at' => null,
                'updated_at' => null
            ];

            if ($i % 1000 == 0) {
                DB::table('documents')->insert($news);
                $news = [];
            }
        }
        DB::table('documents')->insert($news);

        //tbl_quotation
        $this->command->line("documents (from tbl_quotation)");
        $olds = DB::connection('mysql_old')->select('select * from tbl_quotation');
        $news = [];
        foreach ($olds as $i => $old) {
            $fileNames = explode('.', $old->FILE_PATH);
            $extension = end($fileNames);
            $news[] = [
                'documentable_id' => $old->FK_REQUISITIONID,
                'documentable_type' => 'procurements',
                'document_type' => 'Quotation',
                'file_path' => 'uploads/documents/' . $old->FILE_PATH,
                'file_name' => $old->FILE_PATH,
                'file_extension' => $extension,
                'checked' => '1',
                'created_user_id' => null,
                'created_at' => null,
                'updated_at' => null
            ];

            if ($i % 1000 == 0) {
                DB::table('documents')->insert($news);
                $news = [];
            }
        }
        DB::table('documents')->insert($news);


        /*************************
         * Delegations
         ************************* */
        $this->command->line("Delegations (from tbl_routedrequisition)");
        $olds = DB::connection('mysql_old')->select('select * from tbl_routedrequisition where REQUESTED_BY != 0');
        $news = [];
        foreach ($olds as $i => $old) {
            $requisition = null;
            if ($old->REQ_STATUS != 'complete') {
                if ($old->REQ_TYPE == 'purchase') {
                    $requisition = DB::table('purchases')->find($old->FK_REQUISITIONID);
                } else {
                    $requisition = DB::table('procurements')->find($old->FK_REQUISITIONID);
                }
            }
            $news[] = [
                'id' => $old->PK_ROUTEDREQID,
                'delegationable_id' => $old->FK_REQUISITIONID,
                'delegationable_type' => $old->REQ_TYPE == 'purchase' ? 'purchases' : 'procurements',
                'status' => $old->REQ_STATUS == 'complete' ? 'Checked' : 'Pending' ,
                'sender_user_id' => $old->REQUESTED_BY,
                'receiver_user_id' => $old->ACTOR,
                'checked_at' => $old->DATE_PROCESSED,
                'requisition_status_id' => is_null($requisition) ? null : $requisition->requisition_status_id,
                'sender_comment' => $old->COMMENT,
                'receiver_comment' => null,
                'created_at' => $old->DATE_REQUESTED,
                'updated_at' => null
            ];

            if ($i % 1000 == 0) {
                DB::table('delegations')->insert($news);
                $news = [];
            }
        }
        DB::table('delegations')->insert($news);


        //Update trails user delegated user -> sender user & current_user_id
        //FOR PROCUREMENT
        $delegatedProcurementRequisitions = DB::table('delegations')->where([
            ['delegationable_type', 'procurements'],
            ['checked_at', null]
        ])->get();
        foreach ($delegatedProcurementRequisitions as $delegatedProcurementRequisition) {
            //procurements
            Db::table('trails')->where([
                'trailable_type' => 'procurements',
                'trailable_id'=> $delegatedProcurementRequisition->delegationable_id,
                'status' => 'CHECKING'
            ])->whereNotNull('user_id')->update([
                'user_id' => $delegatedProcurementRequisition->sender_user_id,
                'status' => 'DELEGATING'
            ]);
            DB::table('procurements')->where('id', $delegatedProcurementRequisition->delegationable_id)->update(['current_user_id' => $delegatedProcurementRequisition->receiver_user_id]);
            //created purchases form procurement
            $createdPurchases = DB::table('purchases')->where('procurement_id', $delegatedProcurementRequisition->delegationable_id)->get();
            foreach ($createdPurchases as $createdPurchase) {
                Db::table('trails')->where([
                    'trailable_type' => 'purchases',
                    'trailable_id'=> $createdPurchase->id,
                    'status' => 'CHECKING'
                ])->update([
                    'user_id' => $delegatedProcurementRequisition->receiver_user_id,
                ]);
                DB::table('purchases')->where('id', $createdPurchase->id)->update(['current_user_id' => $delegatedProcurementRequisition->receiver_user_id]);
            }
        }
        //PURCHASES
        $delegatedPurchaseRequisitions = DB::table('delegations')->where([
            ['delegationable_type', 'purchases'],
            ['checked_at', null]
        ])->get();
        foreach ($delegatedPurchaseRequisitions as $delegatedPurchaseRequisition) {
            Db::table('trails')->where([
                'trailable_type' => 'purchases',
                'trailable_id'=> $delegatedPurchaseRequisition->delegationable_id,
                'status' => 'CHECKING'
            ])->whereNotNull('user_id')->update([
                'user_id' => $delegatedPurchaseRequisition->sender_user_id,
                'status' => 'DELEGATING'
            ]);
            DB::table('purchases')->where('id', $delegatedPurchaseRequisition->delegationable_id)->update(['current_user_id' => $delegatedPurchaseRequisition->receiver_user_id]);
        }


        /*************************
         * Cancels
         ************************* */
        $this->command->line("Cancels (from tbl_cancelrequisition)");
        $olds = DB::connection('mysql_old')->select('select * from tbl_cancelrequisition');
        $news = [];
        foreach ($olds as $i => $old) {
            $news[] = [
                'id' => $old->PK_CRID,
                'cancelable_id' => $old->FK_REQUISITIONID,
                'cancelable_type' => 'procurements',
                'comment' => $old->COMMENT,
                'created_user_id' => $old->FK_USERID,
                'created_at' => $old->TRANSACTIONDATE,
                'updated_at' => null
            ];
        }
        DB::table('cancels')->insert($news);


        //Update relational tables (Requisition_status, Delete "CHECKING" trails
        $cancelRequisitions = DB::table('cancels')->pluck('cancelable_id')->all();
        Db::table('procurements')->whereIn('id', $cancelRequisitions)->update(['requisition_status_id' => config('const.REQUISITION_STATUS.PROCUREMENT.CANCELED')]);
        Db::table('trails')->where('trailable_type', 'procurements')->whereIn('trailable_id', $cancelRequisitions)->where('status', 'CHECKING')->delete();
        $cancelPurchaseChequeRequisitions = DB::table('purchases')->whereIn('procurement_id', $cancelRequisitions)->where('route', 'Cheque')->pluck('id')->all();
        Db::table('purchases')->whereIn('id', $cancelPurchaseChequeRequisitions)->update(['requisition_status_id' => config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.CANCELED')]);
        Db::table('trails')->where('trailable_type', 'purchases')->whereIn('trailable_id', $cancelPurchaseChequeRequisitions)->where('status', 'CHECKING')->delete();
        $cancelPurchaseLpoRequisitions = DB::table('purchases')->whereIn('procurement_id', $cancelRequisitions)->where('route', 'LPO')->pluck('id')->all();
        Db::table('purchases')->whereIn('id', $cancelPurchaseLpoRequisitions)->update(['requisition_status_id' => config('const.REQUISITION_STATUS.PURCHASE_LPO.CANCELED')]);
        Db::table('trails')->where('trailable_type', 'purchases')->whereIn('trailable_id', $cancelPurchaseLpoRequisitions)->where('status', 'CHECKING')->delete();

        /*************************
         * Notifications
         ************************* */
        // no migration
        // (I made "NotificationTableSeeder", but it does NOT be needed.)
        // $this->call('NotificationTableSeeder');

        /*************************
         * Additional Checking
         ************************* */
        $this->call('AdditionalCheckSeeder');


    }
}
