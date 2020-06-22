<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected static $permissions = [

                'view_settings' => [0],
                'add_settings' => [0],
                'edit_settings' => [0],
                'delete_settings' => [0],

                'manage_states' => [0],

              //        about roles and permissions
                      'view_roles' => [0, 1], 'view_permissions' => [0, 1],
                      'add_roles' => [0, 1], 'add_permissions' => [0, 1],
                      'edit_roles' => [0, 1], 'edit_permissions' => [0, 1],
                      'delete_roles' => [0, 1], 'delete_permissions' => [0, 1],

              //        manage events
                      'view_events' => [0],
                      'add_events' => [0],
                      'edit_events' => [0],
                      'delete_events' => [0],

                   //        manage goods review
                   'view_goods_review' => [0, 1, 2, 3,4,5,6,7],
                   'add_goods_review' => [0, 1, 2, 3,4,5,6,7],
                   'edit_goods_review' => [0, 1, 2, 3,],
                   'delete_goods_review' => [0, 1, 2, 3],

              //        manage goods for vendors, and super admins
                   'view_goods' => [0, 1, 2, 3, 4,6,7],
                   'add_goods' => [0, 4,6,7],
                   'edit_goods' => [0, 4,6,7],
                   'delete_goods' => [0, 4,6,7],

                   //        manage goods for vendors, and super admins
                   'view_orders' => [0, 1, 2, 3, 4,5,6,7],
                   'view_orders_by_other_users' => [0, 1,2,4,6,7],
                   'add_orders' => [0, 3,5],
                   'edit_orders' => [0, 3,5],
                   'delete_orders' => [0, 3,5],

                    //        manage bank
                    'view_banks' => [0, 1, 2, 3,5],
                    'add_banks' => [0, 1],
                    'edit_banks' => [0, 1],
                    'delete_banks' => [0, 1],

                    // Credit
              'view_credits_by_other_users' => [0, 1,2],
              "view_credit" => [0, 1, 2, 3,5],
              "edit_credit" => [0, 3,5],
              "delete_credit" => [0, 1,5],
              "approve_credit" => [0, 1],
              "review_credit" => [0, 1, 2],
              "create_credit" => [0, 1, 3,5],
              "update_credit" => [0, 1, 3,5],

                   //        manage goods for vendors, and super admins
                   'view_dash_goods' => [0, 1, 2, 4,6,7],

                   'view_orders_by_other_users' => [0, 1, 2, 3,5],

                   'view_goods_by_other_users' => [0, 1, 2, 3,5],

              //        manage message templates
                      'view_message_templates' => [0],
                      'add_message_templates' => [0],
                      'edit_message_templates' => [0],
                      'delete_message_templates' => [0],

              //        manage faqs
                      'view_faqs' => [0, 1, 2, 3, 4,5,6,7],
                      'add_faqs' => [0, 1],
                      'edit_faqs' => [0, 1],
                      'delete_faqs' => [0, 1],


                      "supplier_get_subscribed_category" => [4,6,7],
                      "supplier_subscribe_to_category" => [4,6,7],
                      "supplier_view_subscribed_category_detail" => [4,6,7],
                      "delete_supplier_subscribed_category" => [0, 4,6,7],

                      "view_suppliers_categories" => [0, 1, 3,5],

              //        user management
                      "view_staffs" => [0, 1],
                      "view_suppliers" => [0, 1, 2, 3,5],
                      "view_buyers" => [0, 1, 2],
                      "lock_users" => [0, 1],
                      "view_locked_users" => [0, 1],
                      "delete_users" => [0, 1],
                      "change_others_password" => [0, 1],
                      "create_staffs" => [0, 1],
                      "update_staffs" => [0, 1],


                      "view_goods_category" => [0, 1, 2, 3, 4,5,6,7],
                      "view_dash_goods_category" => [0, 1, 2,],
                      "update_goods_category" => [0, 1],
                      "create_goods_category" => [0, 1],
                      "delete_goods_category" => [0, 1],

                      "update_job_titles" => [0, 1, 2],
                      "create_job_titles" => [0, 1, 2],
                      "delete_job_titles" => [0, 1, 2],


                          //warehouses
                          'view_warehouses_by_other_users' => [0],
                          'view_warehouses' => [0, 4,6,7],
                          'add_warehouses' => [0, 4,6,7],
                          'edit_warehouses' => [0, 4,6,7],
                          'delete_warehouses' => [0, 4,6,7],

                      //feedbacks
                      "view_feedbacks" => [0, 1, 2],
                      "delete_feedbacks" => [0],
                      "create_feedbacks" => [0, 1, 2, 3, 4,5,6,7],

                      //responses to feedbacks
                      "respond_to_feedbacks" => [0, 1, 2],
                      "view_responses" => [0, 1, 2],
                      "update_responses" => [0],
                      "delete_responses" => [0],

                      "send_emails" => [0],



                      //notification management
                      "view_notifications" => [0, 1, 2, 3, 4,5,6,7],
                      "delete_notifications" => [0],
                      "create_notifications" => [0],
                      'view_admin_notifications' => [0],

                      "view_quotes" => [0, 1, 2, 3, 4,5,6,7],
                      "delete_quotes" => [0],
                      "create_quotes" => [0, 1, 2,3,4,5,6,7],

                      "view_requested_quotes" => [0, 1, 2, 3, 4,5,6,7],
                      "delete_requested_quotes" => [0],
                      "create_requested_quotes" => [0, 1, 2,3,4,5,6,7],




                       //faq
                      'add_faq' => [0],
                      'edit_faq' => [0],
                      'delete_faq' => [0],


                        //pictures
                      'add_pictures' => [0],
                      'edit_pictures' => [0],
                      'delete_pictures' => [0],
                      'view_pictures' => [0,1,2,3,4,5,6,7],
    ];

    protected $hidden = [
        'guard_name','created_at', 'updated_at'
    ];

    public static function defaultPermissions()
    {
        return self::getPerms(0);
    }

    public static function getAdminPermissions()
    {
        return self::getPerms(1);
    }

    public static function getStaffPermissions()
    {
        return self::getPerms(2);
    }

    public static function getBuyerPermissions()
    {
        return self::getPerms(3);
    }

    public static function getProBuyerPermissions()
    {
        return self::getPerms(5);
    }

    public static function getSellerPermissions()
    {
        return self::getPerms(4);
    }

    public static function getTrustedSellerPermissions()
    {
        return self::getPerms(6);
    }

    public static function getPremiumSellerPermissions()
    {
        return self::getPerms(7);
    }

    /**
     * @return array
     */
    private static function getPerms($id)
    {
        $perms = [];
        foreach (self::$permissions as $key => $arr) {
            if (in_array($id, $arr)) {
                $perms[] = $key;
            }
        }
        return $perms;
    }

    //make sure the permissions sent are available in the db
    public function areValidPermissions(array $permissions = []) : bool
    {
        return $permissions === array_intersect($permissions, $this->pluck('name')->toArray());
    }
}
