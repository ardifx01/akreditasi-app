<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $staff = Staff::paginate(10);
        return view('pages.staff.staf', compact('staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_staf' => 'required|min:5',
            'nama_staff' => 'required|min:5',
            'posisi' => 'required|min:5'
        ]);

        Staff::create([
            'id_staf' => $request->id_staf,
            'nama_staff' => $request->nama_staff,
            'posisi' => $request->posisi
        ]);

        return redirect()->route('staff.index')->with(['success' => 'Data Berhasil Ditambahkan!']);

    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'id_staf' => 'required|min:5',
            'nama_staff' => 'required|string|min:3',
            'posisi' => 'required|min:5'
        ]);
    
        $staff->update([
            'id_staf' => $request->id_staf,
            'nama_staff' => $request->nama_staff,
            'posisi' => $request->posisi
        ]);
    
        return redirect()->back()->with('success', 'Data staff berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('staff.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
