<?php

namespace App\Http\Controllers;


use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $data =  Device::orderBy('id','desc')->paginate(20); 
        return view("device");
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'=>'required','serial_number'=>'required|unique:devices,serial_number',
            'ip_address'=>'nullable|ip','port'=>'nullable|integer',
            'model'=>'nullable','vendor'=>'nullable','push_token'=>'nullable'
        ]);
        return Device::create($data);
    }

    public function show(Device $device){ return $device; }

    public function update(Request $r, Device $device)
    {
        $data = $r->validate([
            'name'=>'sometimes','ip_address'=>'nullable|ip','port'=>'nullable|integer',
            'model'=>'nullable','vendor'=>'nullable','push_token'=>'nullable','is_active'=>'boolean'
        ]);
        $device->update($data);
        return $device;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
