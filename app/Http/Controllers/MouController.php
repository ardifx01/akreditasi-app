<?php

namespace App\Http\Controllers;

use App\Models\Mou;
use App\Models\Staff;
use Illuminate\Http\Request;

class MouController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mou::query();
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('judul_mou', 'like', '%' . $searchTerm . '%')
                ->orWhere('tahun', 'like', '%' . $searchTerm . '%');
        }

        $mou = $query->paginate(10);
        $staffs = Staff::all();

        return view('pages.staff.mou', compact('mou', 'staffs'));

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
            'judul_mou' => 'required',
            'file_dokumen' => 'mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $id = $request->input('judul_mou');
        $dokumen = $request->file('file_dokumen');
        $nama_dok = 'mou_' . $id . '.' . $dokumen->getClientOriginalExtension();
        $dokumen->move('dokumen/', $nama_dok);

        Mou::create([
            'judul_mou' => $request->judul_mou,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Data Mou berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mou $mou)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mou $mou)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mou $mou)
    {
        $request->validate([
            'judul_mou' => 'required',
            'file_dokumen' => 'nullable|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $nama_dok = $mou->file_dokumen;

        if ($request->hasFile('file_dokumen')) {
            $id = $request->input('judul_mou');
            $dokumen = $request->file('file_dokumen');
            $nama_dok = 'mou_' . $id . '.' . $dokumen->getClientOriginalExtension();

            $file_lama = public_path('dokumen/' . $mou->file_dokumen);
            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
            $dokumen->move(public_path('dokumen'), $nama_dok);
        }

        $mou->update([
            'judul_mou' => $request->judul_mou,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'mou berhasil diedit.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mou = Mou::findOrFail($id);
        $file_lama = public_path('dokumen/' . $mou->file_dokumen);
        if (file_exists($file_lama)) {
            unlink($file_lama);
        }
        $mou->delete();
        return redirect()->back()->with('success', 'mou berhasil dihapus.');
    }
}
