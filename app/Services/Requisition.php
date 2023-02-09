<?php

namespace App\Services;

use App\Flow;
use App\Unit;

use App\User;
use App\Order;
use App\Trail;
use App\Travel;
use App\Voucher;
use App\Purchase;
use Carbon\Carbon;
use App\Delegation;
use App\FlowDetail;
use App\Procurement;

use App\Subsistence;
use App\Transport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class Requisition
{
    private $unitConditions = [];
    private $authConditions = [];

    public function __construct()
    {
        $searchLevels = \App\Role::pluck('search_level', 'id')->all();


        // Limit displayed requisitions by user auth
        $unitRoles = Auth::user()->roles()->get();
        $unitConditions = [];
        $authConditions = [];
        foreach ($unitRoles as $unitRole) {
            if ($searchLevels[$unitRole->id] == 'All') {
                $unitConditions = [];
                $authConditions = [];
                break;
            } else if ($searchLevels[$unitRole->id] == 'Unit') {
                $unitConditions[] = $unitRole->pivot->unit_id;
                $authConditions[] = [
                    ['unit_id', '=', $unitRole->pivot->unit_id]
                ];
            } else {
                $unitConditions[] = $unitRole->pivot->unit_id;
                $authConditions[] = [
                    ['unit_id', '=', $unitRole->pivot->unit_id],
                    ['created_user_id', '=', Auth::user()->id]
                ];
            }
        }

        $this->unitConditions = $unitConditions;
        $this->authConditions = $authConditions;
    }

    public function getParams($request)
    {
        //type
        $type = '';
        if ($request->has('type') && $request->filled('type')) {
            $type = $request->type;
        } else if ($request->session()->has('type')) {
            $type = $request->session()->get('type');
        } else {
            $type = 'procurements';
        }
        $request->type = $type;

        //q
        $q = '';
        if ($request->has('q') && $request->filled('q')) {
            $q = $request->q;
        } else if ($request->session()->has('q')) {
            $q = $request->session()->get('q');
        } else {
            $q = 'all';
        }
        $request->q = $q;

        //put session
        $request->session()->put('q', $q);
        $request->session()->put('type', $type);

        return $request;
    }

    /**
     * Get the requisition list
     *
     * @param FormRequest $request
     * @return Eloquent $result
     */
    public function getList($request)
    {
        $params = $this->getParams($request);

        $result = null;

        // DB::enableQueryLog();
        if ($params->type == 'procurements') {
            $result = $this->getProcurementList($params)->paginate(25);
        } else if ($params->type == 'purchases') {
            $result = $this->getPurchaseList($params)->paginate(25);
        } else if ($params->type == 'travels') {
            $result = $this->getTravelList($params)->paginate(25);
        } else if ($params->type == 'subsistences') {
            $result = $this->getSubsistenceList($params)->paginate(25);
        } else if ($params->type == 'transports') {
            $result = $this->getTransportList($params)->paginate(25);
        } else if ($params->type == 'orders') {
            $result = $this->getOrderList($params)->paginate(25);
        } else if ($params->type == 'vouchers') {
            $result = $this->getVoucherList($params)->paginate(25);
        }
        // dd(DB::getQueryLog());

        return $result;
    }

    /**
     * Get the procurement list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */
    private function getProcurementList($params)
    {
        $procurements = Procurement::when($params->q, function ($query, $q) {
            //q
            if ($q == 'mine') {
                //My Requisitions
                $query->where('created_user_id', Auth::user()->id);
            } else if ($q == 'confirmed') {
                //To be confirmed

                // It might work but too slow
                // return $query->whereHas('trails', function ($query) {
                //     $query->where([
                //         ['user_id', Auth::user()->id],
                //         ['status', 'CHECKING']
                //     ]);
                // });

                $ids = Trail::where([
                    ['trailable_type', 'procurements'],
                    ['user_id', Auth::user()->id],
                    ['status', 'CHECKING']
                ])->pluck('trailable_id')->all();

                $delegatedIds = Delegation::where([
                    ['delegationable_type', 'procurements'],
                    ['receiver_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', array_merge($ids, $delegatedIds));
            } else if ($q == 'delegating') {
                //Delegated

                $delegatingIds = Delegation::where([
                    ['delegationable_type', 'procurements'],
                    ['sender_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', $delegatingIds);
            } else {
                //All / Archived

                //Archived
                $query->where('archived', $q == 'archived');

                if (!empty($this->authConditions)) {
                    $query->where(function ($subQuery) {
                        $subQuery->where($this->authConditions[0]);
                        for ($i = 1; $i < count($this->authConditions); $i++) {
                            $subQuery->orWhere($this->authConditions[$i]);
                        }
                    });
                }
                return $query;
            }
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('id', '=', removeFormattedId($id));
        })->when($params->title, function ($query, $title) {
            //title
            return $query->where('title', 'like', "%{$title}%");
        })->when($params->owner, function ($query, $owner) {
            //owner
            return $query->whereHas('createdUser', function ($query) use ($owner) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$owner}%"]);
            });
        })->when($params->unitCategory, function ($query, $unitCategory) {
            //unit category (UNIT or PROJECT)
            return $query->whereHas('unit', function ($query) use ($unitCategory) {
                $query->where('category',  '=', $unitCategory);
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHas('unit', function ($query) use ($unit) {
                $query->where('name',  'like', "%{$unit}%");
            });
        })->when($params->status, function ($query, $status) {
            //requisition status
            if ($status == 'inProgress') {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->whereNotIn('name', ['Closed', 'Canceled']);
                });
            } else {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->where('name', '=', $status);
                });
            }
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->updated, function ($query, $updated) {
            //updated
            return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
        })->when($params->current, function ($query, $current) {
            //current user
            return $query->whereHas('currentUser', function ($query) use ($current) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
            });
        });

        //sort
        $order = $params->filled('order') ? $params->order : 'updated';
        $dir = $params->filled('dir') ? $params->dir : 'desc';
        $procurements = $procurements->when($order, function ($query, $order) use ($dir) {
            if ($order == 'id') {
                //id
                return $query->orderBy('id', $dir);
            } else if ($order == 'created') {
                //created
                return $query->orderBy('created_at', $dir);
            } else if ($order == 'updated') {
                //updated
                return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
            }
        });

        return $procurements;
    }

    /**
     * Get the purchase list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */

    private function getPurchaseList($params)
    {
        $purchases = Purchase::when($params->q, function ($query, $q) {
            if ($q == 'mine') {
                //My Requisitions
                return $query->whereHas('procurement', function ($query) {
                    $query->where('created_user_id', Auth::user()->id);
                });
            } else if ($q == 'confirmed') {
                //To be confirmed
                $ids = Trail::where([
                    ['trailable_type', 'purchases'],
                    ['user_id', Auth::user()->id],
                    ['status', 'CHECKING']
                ])->pluck('trailable_id')->all();

                $delegatedIds = Delegation::where([
                    ['delegationable_type', 'purchases'],
                    ['receiver_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', array_merge($ids, $delegatedIds));
            } else if ($q == 'delegating') {
                //Delegated

                $delegatingIds = Delegation::where([
                    ['delegationable_type', 'purchases'],
                    ['sender_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', $delegatingIds);
            } else {
                //All / Archived

                //Archived
                $query->whereHas('procurement', function ($subQuery) use ($q) {
                    $subQuery->where('archived', $q == 'archived');
                });


                if (!empty($this->authConditions)) {
                    $query->whereHas('procurement', function ($subQuery) {
                        $subQuery->where($this->authConditions[0]);
                        for ($i = 1; $i < count($this->authConditions); $i++) {
                            $subQuery->orWhere($this->authConditions[$i]);
                        }
                    });
                }
                return $query;
            }
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('id', '=', removeFormattedId($id));
        })->when($params->procurement_id, function ($query, $procurement_id) {
            //procurement_id
            return $query->where('procurement_id', '=', removeFormattedId($procurement_id));
        })->when($params->route, function ($query, $route) {
            //route
            return $query->where('route', 'like', "%{$route}%");
        })->when($params->title, function ($query, $title) {
            //title
            return $query->whereHas('procurement', function ($query) use ($title) {
                $query->where('title', 'like', "%{$title}%");
            });
        })->when($params->unitCategory, function ($query, $unitCategory) {
            //unit category (UNIT or PROJECT)
            return $query->whereHas('procurement', function ($query) use ($unitCategory) {
                return $query->whereHas('unit', function ($query) use ($unitCategory) {
                    $query->where('category',  '=', $unitCategory);
                });
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHas('procurement', function ($query) use ($unit) {
                $query->whereHas('unit', function ($query) use ($unit) {
                    $query->where('name',  'like', "%{$unit}%");
                });
            });
        })->when($params->supplier, function ($query, $supplier) {
            //supplier
            return $query->whereHas('supplier', function ($query) use ($supplier) {
                $query->where('name', 'like', "%{$supplier}%");
            });
        })->when($params->current, function ($query, $current) {
            //current user
            return $query->whereHas('currentUser', function ($query) use ($current) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
            });
        })->when($params->status, function ($query, $status) {
            //requisition status
            if ($status == 'inProgress') {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->whereNotIn('name', ['Closed', 'Canceled']);
                });
            } else {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->where('name',  '=', $status);
                });
            }
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->updated, function ($query, $updated) {
            //updated
            return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
        });

        //sort
        $order = $params->filled('order') ? $params->order : 'updated';
        $dir = $params->filled('dir') ? $params->dir : 'desc';
        $purchases = $purchases->when($order, function ($query, $order) use ($dir) {
            if ($order == 'id') {
                //id
                return $query->orderBy('id', $dir);
            } else if ($order == 'procurement_id') {
                //procurement_id
                return $query->orderBy('procurement_id', $dir);
            } else if ($order == 'created') {
                //created
                return $query->orderBy('created_at', $dir);
            } else if ($order == 'updated') {
                //updated
                return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
            }
        });

        return $purchases;
    }

    /**
     * Get the order list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */

    private function getOrderList($params)
    {
        $orders = new Order();

        return $orders;
    }

    /**
     * Get the voucher list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */

    private function getVoucherList($params)
    {
        $vouchers = [];

        // Limit displayed by user auth
        if (Auth::user()->can('voucher')) {

            $vouchers = Voucher::when($params->q, function ($query, $q) {
                if ($q == 'mine') {
                    //My Requisitions
                    $query->where('created_user_id', Auth::user()->id);
                } else if ($q == 'confirmed') {
                    //To be confirmed
                    $ids = Trail::where([
                        ['trailable_type', 'vouchers'],
                        ['user_id', Auth::user()->id],
                        ['status', 'CHECKING']
                    ])->pluck('trailable_id')->all();

                    $query->whereIn('id', $ids);
                } else if ($q == 'delegating') {
                    //Delegated

                    //nothing
                    $query->where('id', -1);
                } else {
                    //All / Archived

                    //Archived
                    return $query->whereHas('purchase', function ($query) use ($q) {
                        return $query->whereHas('procurement', function ($query) use ($q) {
                            $query->where('archived', $q == 'archived');
                        });
                    });

                    return $query;
                }
            })->when($params->id, function ($query, $id) {
                //id
                return $query->where('id', '=', removeFormattedId($id));
            })->when($params->purchase_id, function ($query, $purchase_id) {
                //purchase_id
                return $query->where('purchase_id', '=', removeFormattedId($purchase_id));
            })->when($params->title, function ($query, $title) {
                //title
                return $query->whereHas('purchase', function ($query) use ($title) {
                    return $query->whereHas('procurement', function ($query) use ($title) {
                        $query->where('title', 'like', "%{$title}%");
                    });
                });
            })->when($params->unitCategory, function ($query, $unitCategory) {
                //unit category (UNIT or PROJECT)
                return $query->whereHas('purchase', function ($query) use ($unitCategory) {
                    return $query->whereHas('procurement', function ($query) use ($unitCategory) {
                        return $query->whereHas('unit', function ($query) use ($unitCategory) {
                            $query->where('category',  '=', $unitCategory);
                        });
                    });
                });
            })->when($params->unit, function ($query, $unit) {
                //department / unit
                return $query->whereHas('purchase', function ($query) use ($unit) {
                    return $query->whereHas('procurement', function ($query) use ($unit) {
                        return $query->whereHas('unit', function ($query) use ($unit) {
                            $query->where('name',  'like', "%{$unit}%");
                        });
                    });
                });
            })->when($params->supplier, function ($query, $supplier) {
                //supplier
                return $query->whereHas('purchase', function ($query) use ($supplier) {
                    return $query->whereHas('supplier', function ($query) use ($supplier) {
                        $query->where('name', 'like', "%{$supplier}%");
                    });
                });
            })->when($params->current, function ($query, $current) {
                //current user
                return $query->whereHas('currentUser', function ($query) use ($current) {
                    $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
                });
            })->when($params->status, function ($query, $status) {
                //requisition status
                if ($status == 'inProgress') {
                    return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                        $query->whereNotIn('name', ['Closed', 'Canceled']);
                    });
                } else {
                    return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                        $query->where('name',  '=', $status);
                    });
                }
            })->when($params->created, function ($query, $created) {
                //created
                return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
            })->when($params->updated, function ($query, $updated) {
                //updated
                return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
            });

            //sort
            $order = $params->filled('order') ? $params->order : 'updated';
            $dir = $params->filled('dir') ? $params->dir : 'desc';
            $vouchers = $vouchers->when($order, function ($query, $order) use ($dir) {
                if ($order == 'id') {
                    //id
                    return $query->orderBy('id', $dir);
                } else if ($order == 'purchase_id') {
                    //purchase_id
                    return $query->orderBy('purchase_id', $dir);
                } else if ($order == 'created') {
                    //created
                    return $query->orderBy('created_at', $dir);
                } else if ($order == 'updated') {
                    //updated
                    return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
                }
            });
        }

        return $vouchers;
    }

    /**
     * Get the transport list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */
    private function getTransportList($params)
    {
        $transports = Transport::when($params->q, function ($query, $q) {
            if ($q == 'mine') {
                //My Requisitions
                return $query->whereHas('travel.procurement', function ($query) {
                    $query->where('created_user_id', Auth::user()->id);
                });
            } else if ($q == 'confirmed') {
                //To be confirmed
                $ids = Trail::where([
                    ['trailable_type', 'transports'],
                    ['user_id', Auth::user()->id],
                    ['status', 'CHECKING']
                ])->pluck('trailable_id')->all();

                $delegatedIds = Delegation::where([
                    ['delegationable_type', 'transports'],
                    ['receiver_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', array_merge($ids, $delegatedIds));
            } else if ($q == 'delegating') {
                //Delegated

                $delegatingIds = Delegation::where([
                    ['delegationable_type', 'transports'],
                    ['sender_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', $delegatingIds);
            } else {
                //All / Archived

                //Archived
                $query->whereHas('travel.procurement', function ($subQuery) use ($q) {
                    $subQuery->where('archived', $q == 'archived');
                });


                if (!empty($this->authConditions)) {
                    $query->whereHas('travel.procurement', function ($subQuery) {
                        $subQuery->where($this->authConditions[0]);
                        for ($i = 1; $i < count($this->authConditions); $i++) {
                            $subQuery->orWhere($this->authConditions[$i]);
                        }
                    });
                }
                return $query;
            }
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('id', '=', removeFormattedId($id));
        })->when($params->procurement_id, function ($query, $procurement_id) {
            //procurement_id
            return $query->where('procurement_id', '=', removeFormattedId($procurement_id));
        })->when($params->route, function ($query, $route) {
            //route
            return $query->where('route', 'like', "%{$route}%");
        })->when($params->title, function ($query, $title) {
            //title
            return $query->whereHas('travel.procurement', function ($query) use ($title) {
                $query->where('title', 'like', "%{$title}%");
            });
        })->when($params->unitCategory, function ($query, $unitCategory) {
            //unit category (UNIT or PROJECT)
            return $query->whereHas('travel.procurement', function ($query) use ($unitCategory) {
                return $query->whereHas('unit', function ($query) use ($unitCategory) {
                    $query->where('category',  '=', $unitCategory);
                });
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHas('travel.procurement', function ($query) use ($unit) {
                $query->whereHas('unit', function ($query) use ($unit) {
                    $query->where('name',  'like', "%{$unit}%");
                });
            });
        })->when($params->supplier, function ($query, $supplier) {
            //supplier
            return $query->whereHas('supplier', function ($query) use ($supplier) {
                $query->where('name', 'like', "%{$supplier}%");
            });
        })->when($params->current, function ($query, $current) {
            //current user
            return $query->whereHas('currentUser', function ($query) use ($current) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
            });
        })->when($params->status, function ($query, $status) {
            //requisition status
            if ($status == 'inProgress') {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->whereNotIn('name', ['Closed', 'Canceled']);
                });
            } else {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->where('name',  '=', $status);
                });
            }
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->updated, function ($query, $updated) {
            //updated
            return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
        });

        //sort
        $order = $params->filled('order') ? $params->order : 'updated';
        $dir = $params->filled('dir') ? $params->dir : 'desc';
        $transports = $transports->when($order, function ($query, $order) use ($dir) {
            if ($order == 'id') {
                //id
                return $query->orderBy('id', $dir);
            } else if ($order == 'procurement_id') {
                //procurement_id
                return $query->orderBy('procurement_id', $dir);
            } else if ($order == 'created') {
                //created
                return $query->orderBy('created_at', $dir);
            } else if ($order == 'updated') {
                //updated
                return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
            }
        });

        return $transports;
    }

    /**
     * Get the subsistence list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */
    private function getSubsistenceList($params)
    {
        $subsistences = Subsistence::when($params->q, function ($query, $q) {
            if ($q == 'mine') {
                //My Requisitions
                return $query->whereHas('travel.procurement', function ($query) {
                    $query->where('created_user_id', Auth::user()->id);
                });
            } else if ($q == 'confirmed') {
                //To be confirmed
                $ids = Trail::where([
                    ['trailable_type', 'subsistences'],
                    ['user_id', Auth::user()->id],
                    ['status', 'CHECKING']
                ])->pluck('trailable_id')->all();

                $delegatedIds = Delegation::where([
                    ['delegationable_type', 'subsistences'],
                    ['receiver_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', array_merge($ids, $delegatedIds));
            } else if ($q == 'delegating') {
                //Delegated

                $delegatingIds = Delegation::where([
                    ['delegationable_type', 'subsistences'],
                    ['sender_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', $delegatingIds);
            } else {
                //All / Archived

                //Archived
                $query->whereHas('travel.procurement', function ($subQuery) use ($q) {
                    $subQuery->where('archived', $q == 'archived');
                });


                if (!empty($this->authConditions)) {
                    $query->whereHas('travel.procurement', function ($subQuery) {
                        $subQuery->where($this->authConditions[0]);
                        for ($i = 1; $i < count($this->authConditions); $i++) {
                            $subQuery->orWhere($this->authConditions[$i]);
                        }
                    });
                }
                return $query;
            }
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('id', '=', removeFormattedId($id));
        })->when($params->procurement_id, function ($query, $procurement_id) {
            //procurement_id
            return $query->where('procurement_id', '=', removeFormattedId($procurement_id));
        })->when($params->route, function ($query, $route) {
            //route
            return $query->where('route', 'like', "%{$route}%");
        })->when($params->title, function ($query, $title) {
            //title
            return $query->whereHas('travel.procurement', function ($query) use ($title) {
                $query->where('title', 'like', "%{$title}%");
            });
        })->when($params->unitCategory, function ($query, $unitCategory) {
            //unit category (UNIT or PROJECT)
            return $query->whereHas('travel.procurement', function ($query) use ($unitCategory) {
                return $query->whereHas('unit', function ($query) use ($unitCategory) {
                    $query->where('category',  '=', $unitCategory);
                });
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHas('travel.procurement', function ($query) use ($unit) {
                $query->whereHas('unit', function ($query) use ($unit) {
                    $query->where('name',  'like', "%{$unit}%");
                });
            });
        })->when($params->supplier, function ($query, $supplier) {
            //supplier
            return $query->whereHas('supplier', function ($query) use ($supplier) {
                $query->where('name', 'like', "%{$supplier}%");
            });
        })->when($params->current, function ($query, $current) {
            //current user
            return $query->whereHas('currentUser', function ($query) use ($current) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
            });
        })->when($params->status, function ($query, $status) {
            //requisition status
            if ($status == 'inProgress') {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->whereNotIn('name', ['Closed', 'Canceled']);
                });
            } else {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->where('name',  '=', $status);
                });
            }
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->updated, function ($query, $updated) {
            //updated
            return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
        });

        //sort
        $order = $params->filled('order') ? $params->order : 'updated';
        $dir = $params->filled('dir') ? $params->dir : 'desc';
        $subsistences = $subsistences->when($order, function ($query, $order) use ($dir) {
            if ($order == 'id') {
                //id
                return $query->orderBy('id', $dir);
            } else if ($order == 'procurement_id') {
                //procurement_id
                return $query->orderBy('procurement_id', $dir);
            } else if ($order == 'created') {
                //created
                return $query->orderBy('created_at', $dir);
            } else if ($order == 'updated') {
                //updated
                return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
            }
        });

        return $subsistences;
    }

    /**
     * Get the travel list
     *
     * @param FormRequest $params
     * @return Eloquent $result
     */
    private function getTravelList($params)
    {
        $travels = Travel::when($params->q, function ($query, $q) {
            if ($q == 'mine') {
                //My Requisitions
                return $query->whereHas('procurement', function ($query) {
                    $query->where('created_user_id', Auth::user()->id);
                });
            } else if ($q == 'confirmed') {
                //To be confirmed
                $ids = Trail::where([
                    ['trailable_type', 'travels'],
                    ['user_id', Auth::user()->id],
                    ['status', 'CHECKING']
                ])->pluck('trailable_id')->all();

                $delegatedIds = Delegation::where([
                    ['delegationable_type', 'travels'],
                    ['receiver_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', array_merge($ids, $delegatedIds));
            } else if ($q == 'delegating') {
                //Delegated

                $delegatingIds = Delegation::where([
                    ['delegationable_type', 'travels'],
                    ['sender_user_id', Auth::user()->id],
                    ['status', 'Pending']
                ])->pluck('delegationable_id')->all();

                $query->whereIn('id', $delegatingIds);
            } else {
                //All / Archived

                //Archived
                $query->whereHas('procurement', function ($subQuery) use ($q) {
                    $subQuery->where('archived', $q == 'archived');
                });


                if (!empty($this->authConditions)) {
                    $query->whereHas('procurement', function ($subQuery) {
                        $subQuery->where($this->authConditions[0]);
                        for ($i = 1; $i < count($this->authConditions); $i++) {
                            $subQuery->orWhere($this->authConditions[$i]);
                        }
                    });
                }
                return $query;
            }
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('id', '=', removeFormattedId($id));
        })->when($params->procurement_id, function ($query, $procurement_id) {
            //procurement_id
            return $query->where('procurement_id', '=', removeFormattedId($procurement_id));
        })->when($params->route, function ($query, $route) {
            //route
            return $query->where('route', 'like', "%{$route}%");
        })->when($params->title, function ($query, $title) {
            //title
            return $query->whereHas('procurement', function ($query) use ($title) {
                $query->where('title', 'like', "%{$title}%");
            });
        })->when($params->unitCategory, function ($query, $unitCategory) {
            //unit category (UNIT or PROJECT)
            return $query->whereHas('procurement', function ($query) use ($unitCategory) {
                return $query->whereHas('unit', function ($query) use ($unitCategory) {
                    $query->where('category',  '=', $unitCategory);
                });
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHas('procurement', function ($query) use ($unit) {
                $query->whereHas('unit', function ($query) use ($unit) {
                    $query->where('name',  'like', "%{$unit}%");
                });
            });
        })->when($params->supplier, function ($query, $supplier) {
            //supplier
            return $query->whereHas('supplier', function ($query) use ($supplier) {
                $query->where('name', 'like', "%{$supplier}%");
            });
        })->when($params->current, function ($query, $current) {
            //current user
            return $query->whereHas('currentUser', function ($query) use ($current) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$current}%"]);
            });
        })->when($params->status, function ($query, $status) {
            //requisition status
            if ($status == 'inProgress') {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->whereNotIn('name', ['Closed', 'Canceled']);
                });
            } else {
                return $query->whereHas('requisitionStatus', function ($query) use ($status) {
                    $query->where('name',  '=', $status);
                });
            }
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->updated, function ($query, $updated) {
            //updated
            return $query->whereBetween('updated_at', [new Carbon($updated['start']), new Carbon($updated['end'] . ' 23:59:59')]);
        });

        //sort
        $order = $params->filled('order') ? $params->order : 'updated';
        $dir = $params->filled('dir') ? $params->dir : 'desc';
        $travels = $travels->when($order, function ($query, $order) use ($dir) {
            if ($order == 'id') {
                //id
                return $query->orderBy('id', $dir);
            } else if ($order == 'procurement_id') {
                //procurement_id
                return $query->orderBy('procurement_id', $dir);
            } else if ($order == 'created') {
                //created
                return $query->orderBy('created_at', $dir);
            } else if ($order == 'updated') {
                //updated
                return $query->orderBy('updated_at', $dir)->orderBy('id', $dir);
            }
        });

        return $travels;
    }
    /**
     * count confirmed requisitions
     *
     * @return Eloquent $counts
     */
    public function getConfirmedCount()
    {

        $trailCounts = Trail::select('trailable_type', DB::raw('count(*) as count'))
            ->where([
                ['user_id', Auth::user()->id],
                ['status', 'CHECKING'],
                ['trailable_type', '!=', 'orders'],
            ])
            ->whereHasMorph('trailable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
                //Not Archived
                if ($type == Procurement::class) {
                    $query->where('archived', false);
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                }
            })
            ->groupBy('trailable_type')
            ->pluck('count', 'trailable_type')->all();

        $delegatedCounts = Delegation::select('delegationable_type', DB::raw('count(*) as count'))
            ->where([
                ['receiver_user_id', Auth::user()->id],
                ['status', 'Pending']
            ])
            ->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
                //Not Archived
                if ($type == Procurement::class) {
                    $query->where('archived', false);
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                }
            })
            ->groupBy('delegationable_type')
            ->pluck('count', 'delegationable_type')->all();

        $counts = [];
        foreach (array_merge($trailCounts, $delegatedCounts) as $key => $value) {
            $counts[$key] = ($trailCounts[$key] ?? 0) + ($delegatedCounts[$key] ?? 0);
        }

        return $counts;
    }

    /**
     * Count delegated requisitions
     *
     * @return number $count
     */
    public function getDelegatedCount()
    {
        $delegatedCount = Delegation::where([
            ['receiver_user_id', Auth::user()->id],
            ['status', 'Pending']
        ])
            ->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
                //Not Archived
                if ($type == Procurement::class) {
                    $query->where('archived', false);
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                }
            })
            ->count();

        return $delegatedCount;
    }

    /**
     * Count requisitions number of in progress
     *
     * @return number $count
     */
    public function getInProgressCount()
    {
        $procurementCount = Procurement::where('created_user_id', Auth::user()->id)
            ->whereNotIn('requisition_status_id', [
                config('const.REQUISITION_STATUS.PROCUREMENT.CLOSED'),
                config('const.REQUISITION_STATUS.PROCUREMENT.CANCELED')
            ])
            ->where('archived', 0)
            ->count();
        $travelCount = 0;
        $transportCount = 0;
        $subsistenceCount = 0;
        $maintenanceCount = 0;

        $count = $procurementCount + $travelCount + $transportCount + $subsistenceCount + $maintenanceCount;

        return $count;
    }

    /**
     * Count completed requisitions
     *
     * @return number $count
     */
    public function getCompletedCount()
    {
        $procurementCount = Procurement::where('created_user_id', Auth::user()->id)
            ->where('requisition_status_id', config('const.REQUISITION_STATUS.PROCUREMENT.CLOSED'))
            ->where('archived', 0)
            ->count();
        $travelCount = Travel::where('created_user_id', Auth::user()->id)
            ->where('requisition_status_id', config('const.REQUISITION_STATUS.TRAVEL.CLOSED'))
            //->where('archived', 0)
            ->count();
        $transportCount = 0;
        $subsistenceCount = 0;
        $maintenanceCount = 0;

        $count = $procurementCount + $travelCount + $transportCount + $subsistenceCount + $maintenanceCount;

        return $count;
    }

    /**
     * get units which the login user can create target module requisitions
     *
     * @param number $moduleId
     * @return collection units
     */
    public function getCreatableRequisitionUnits($moduleId)
    {

        $allUnits = Auth::user()->units()->get();

        $units = collect();
        foreach ($allUnits as $unit) {
            $companyId = $unit->company->id;
            $creatable = Flow::where([
                ['module_id', $moduleId],
                ['company_id', $companyId]
            ])->whereHas('flowDetails', function ($query) use ($unit) {
                $query->where([
                    ['level', 1],
                    ['role_id', $unit->pivot->role_id]
                ]);
            })->count();

            if ($creatable > 0) {
                $units->push($unit);
            }
        }

        return $units;
    }

    /**
     * get requisition search parameter lists
     *
     * @param string $type search type (procurement, purchase, order ...)
     * @return array search list
     */
    public function getSearchLists($type)
    {

        //requisition status
        $moduleIds = [];
        if ($type == 'procurements') {
            $moduleIds[] = config('const.MODULE.PROCUREMENT.PROCUREMENT');
        } else if ($type == 'purchases') {
            $moduleIds[] = config('const.MODULE.PROCUREMENT.PURCHASE_LPO');
            $moduleIds[] = config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE');
        } else if ($type == 'travels') {
            $moduleIds[] = config('const.MODULE.TRAVEL.TRAVEL');
        } else if ($type == 'transports') {
            $moduleIds[] = config('const.MODULE.TRAVEL.TRANSPORT');
        } else if ($type == 'subsistences') {
            $moduleIds[] = config('const.MODULE.TRAVEL.SUBSISTENCE');
        } else if ($type == 'orders') {
            $moduleIds[] = config('const.MODULE.PROCUREMENT.ORDER');
        } else if ($type == 'vouchers') {
            $moduleIds[] = config('const.MODULE.PROCUREMENT.VOUCHER');
        }
        $requisitionStatus = \App\RequisitionStatus::where('module_id', $moduleIds)->pluck('name', 'name')->all();
        $requisitionStatus['inProgress'] = '(In Progress)';

        return compact('requisitionStatus');
    }

    /**
     * Get the delegation list
     *
     * @param FormRequest $request
     * @return Eloquent $result
     */
    public function getDelegationList($request)
    {

        $params = $request;

        // DB::enableQueryLog();

        $delegations = Delegation::where(function ($query) {
            $query->where('receiver_user_id', Auth::user()->id)
                ->orWhere('sender_user_id', Auth::user()->id);
        })->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
            //Not Archived
            if ($type == Procurement::class) {
                $query->where('archived', false);
            } else if ($type == Purchase::class) {
                return $query->whereHas('procurement', function ($query) {
                    $query->where('archived', false);
                });
            } else if ($type == Travel::class) {
                return $query->whereHas('procurement', function ($query) {
                    $query->where('archived', false);
                });
            }
        })->when($params->type, function ($query, $type) {
            //type
            return $query->where('delegationable_type', '=', $type);
        })->when($params->id, function ($query, $id) {
            //id
            return $query->where('delegationable_id', '=', removeFormattedId($id));
        })->when($params->title, function ($query, $title) {
            //title
            return $query->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) use ($title) {
                if ($type == Procurement::class) {
                    $query->where('title', 'like', "%{$title}%");
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) use ($title) {
                        $query->where('title', 'like', "%{$title}%");
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) use ($title) {
                        $query->where('title', 'like', "%{$title}%");
                    });
                }
            });
        })->when($params->owner, function ($query, $owner) {
            //Owner
            return $query->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) use ($owner) {
                if ($type == Procurement::class) {
                    return $query->whereHas('createdUser', function ($query) use ($owner) {
                        $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$owner}%"]);
                    });
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) use ($owner) {
                        return $query->whereHas('createdUser', function ($query) use ($owner) {
                            $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$owner}%"]);
                        });
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) use ($owner) {
                        return $query->whereHas('createdUser', function ($query) use ($owner) {
                            $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$owner}%"]);
                        });
                    });
                }
            });
        })->when($params->unit, function ($query, $unit) {
            //department / unit
            return $query->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) use ($unit) {
                if ($type == Procurement::class) {
                    return $query->whereHas('unit', function ($query) use ($unit) {
                        $query->where('name',  'like', "%{$unit}%");
                    });
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) use ($unit) {
                        return $query->whereHas('unit', function ($query) use ($unit) {
                            $query->where('name',  'like', "%{$unit}%");
                        });
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) use ($unit) {
                        return $query->whereHas('unit', function ($query) use ($unit) {
                            $query->where('name',  'like', "%{$unit}%");
                        });
                    });
                }
            });
        })->when($params->status, function ($query, $status) {
            //delegation status
            $query->where('status', '=', $status);
        })->when($params->created, function ($query, $created) {
            //created
            return $query->whereBetween('created_at', [new Carbon($created['start']), new Carbon($created['end'] . ' 23:59:59')]);
        })->when($params->sender, function ($query, $sender) {
            //sender
            return $query->whereHas('sender', function ($query) use ($sender) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$sender}%"]);
            });
        })->when($params->receiver, function ($query, $receiver) {
            //receiver
            return $query->whereHas('receiver', function ($query) use ($receiver) {
                $query->whereRaw("concat_ws(' ',first_name, surname) like ?", ["%{$receiver}%"]);
            });
        })->orderBy('status', 'asc')->orderBy('created_at', 'desc')->paginate(25);

        // dd(DB::getQueryLog());

        return $delegations;
    }

    public function getNextConfirmedCount($nextUser = NULL)
    {

        $trailCounts = Trail::select('trailable_type', DB::raw('count(*) as count'))
            ->where([
                ['user_id', $nextUser->id],
                ['status', 'CHECKING'],
                ['trailable_type', '!=', 'orders'],
            ])
            ->whereHasMorph('trailable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
                //Not Archived
                if ($type == Procurement::class) {
                    $query->where('archived', false);
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                }
            })
            ->groupBy('trailable_type')
            ->pluck('count', 'trailable_type')->all();

        $delegatedCounts = Delegation::select('delegationable_type', DB::raw('count(*) as count'))
            ->where([
                ['receiver_user_id', $nextUser->id],
                ['status', 'Pending']
            ])
            ->whereHasMorph('delegationable', [Procurement::class, Purchase::class, Travel::class, Subsistence::class, Transport::class], function ($query, $type) {
                //Not Archived
                if ($type == Procurement::class) {
                    $query->where('archived', false);
                } else if ($type == Purchase::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                } else if ($type == Travel::class) {
                    return $query->whereHas('procurement', function ($query) {
                        $query->where('archived', false);
                    });
                }
            })
            ->groupBy('delegationable_type')
            ->pluck('count', 'delegationable_type')->all();

        $counts = [];
        foreach (array_merge($trailCounts, $delegatedCounts) as $key => $value) {
            $counts[$key] = ($trailCounts[$key] ?? 0) + ($delegatedCounts[$key] ?? 0);
        }

        return $counts;
    }



    /**
     * get next/previous trail user
     *
     * @param Procurement $procurement
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers(bool $sign, ?FlowDetail $currentFlowDetail, Unit $unit, User $owner = null)
    {
        
        $nextFlow = null;
        $nextUsers = [];

        if (!empty($currentFlowDetail)) {
            $nextLevel = $sign ? $currentFlowDetail->level + 1 : $currentFlowDetail->level - 1;
            $nextFlow = FlowDetail::where([
                ['flow_id', $currentFlowDetail->flow_id],
                ['level', $nextLevel]
            ])->whereNull('deleted_at')->first();

            if (!empty($nextFlow) && !empty($nextFlow->role_id)) {
                if ($nextFlow->role_id == 2 && !empty($owner)) {
                    //owner
                    $nextUsers = new \Illuminate\Database\Eloquent\Collection([$owner]);
                } else {
                    // get user belongs the unit
                    $nextUsers = new \Illuminate\Database\Eloquent\Collection;
                
                    if($owner->campus){
                        $nextUsers = User::whereHas('campus', function ($query) use ($owner) {
                            $query->where([
                                ['district_id', $owner->campus->district->id],
                            ]);
                        })->whereHas('units', function ($query) use ($unit, $nextFlow) {
                            $query->where([
                                ['id', $unit->id],
                                ['role_id', $nextFlow->role_id],
                            ]);
                        })->get();
                    }

                    if ($nextUsers->isEmpty()) {
                        // get user belongs the unit
                        $nextUsers = User::whereHas('units', function ($query) use ($unit, $nextFlow) {
                            $query->where([
                                ['id', $unit->id],
                                ['role_id', $nextFlow->role_id],
                            ]);
                        })->get();

                        if ($nextUsers->isEmpty()) {
                            // get user for all unit
                            $nextUsers = User::whereHas('roles', function ($query) use ($nextFlow) {
                                $query->where([
                                    ['id', $nextFlow->role_id],
                                    ['single_user', 1],
                                ]);
                            })->get();

                            if ($nextUsers->isEmpty()) {
                                //search to expand the company
                                $companyId = $unit->company->id;
                                $units = Unit::where('company_id', $companyId)->pluck('id')->all();
                                $nextUsers = User::whereHas('units', function ($query) use ($units, $nextFlow) {
                                    $query
                                        ->whereIn('id', $units)
                                        ->where('role_id', $nextFlow->role_id);
                                })->get();

                                if ($nextUsers->isEmpty()) {
                                    // dd($nextFlow);
                                    //Investigation required (要調査)
                                    throw new \Exception('Wrong trail');
                                }
                            }
                        }
                    }
                }
                
            }
        }

        // if(count($nextUsers) > 1){
        //     $index = [];
        //     foreach($nextUsers as $nextUser){
        //         array_push($index, array_sum($this->getNextConfirmedCount($nextUser)));
        //     }
        //     $nextUsers = new \Illuminate\Database\Eloquent\Collection([$nextUsers[array_search(min($index), $index)]]);
            
        // }

        if(count($nextUsers) > 1){
            $index = [];
            $priority = true;
            $modules = collect(config('const.MODULE.PROCUREMENT') + config('const.MODULE.TRAVEL'));
            $nextUsers = User::whereHas('priority', function ($query) use ($nextFlow) {
                $query->where([
                    ['role_id', $nextFlow->role_id],
                ]);
            })->get();
            foreach($nextUsers as $nextUser){
                if($nextFlow->flow->module_id == $nextUser->priority->module_id){
                    if(array_sum($this->getNextConfirmedCount($nextUser)) < 20){
                        $nextUsers = new \Illuminate\Database\Eloquent\Collection([$nextUser]);
                        break;
                    } else {
                        $priority = false;
                        array_push($index, array_sum($this->getNextConfirmedCount($nextUser)));
                    }
                    
                } else {
                    $priority = false;
                    array_push($index, array_sum($this->getNextConfirmedCount($nextUser)));
                }
                
            }

            if(!$priority){
                dd($nextUsers = new \Illuminate\Database\Eloquent\Collection([$nextUsers[array_search(min($index), $index)]]));
            }
            
           
            
        }
        return ['flow' => $nextFlow, 'users' => $nextUsers];
    }
}
