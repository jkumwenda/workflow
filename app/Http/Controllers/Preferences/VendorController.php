<?php

namespace App\Http\Controllers\Preferences;

use App\Http\Controllers\Controller;

use App\Supplier;
use App\SupplierEvaluation;
use App\Purchase;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::get();
        return view('preferences.vendor.index',compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('preferences.vendor.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:suppliers',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        Supplier::create($request->all());

        $request->session()->flash('message', 'Successfully created');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vendors.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        return view('preferences.vendor.edit', compact('supplier')) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('suppliers')->ignore($id),
            ],
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        Supplier::findOrFail($id)->update($request->all());

        $request->session()->flash('message', 'Successfully updated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vendors.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //Validation
        $ordered = Purchase::where('supplier_id', $id)->count();
        if ($ordered > 0) {
            $request->session()->flash('message', 'Already ordered. Can not delete.');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('vendors.index');
        }

        $supplier = Supplier::findOrFail($id);
        try {
            $supplier->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('vendors.index');
        }

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vendors.index');
    }
    /**
     * Display evaluations/comments of supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function comments(Request $request, $supplier_id)
    {
        $supplier = Supplier::findOrFail($supplier_id);
        $supplierEvaluations = SupplierEvaluation::where('supplier_id', $supplier_id)->get();

        return view('preferences.vendor.comments',compact('supplier', 'supplierEvaluations'));
    }
}
