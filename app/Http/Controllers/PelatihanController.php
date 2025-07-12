<?php

namespace App\Http\Controllers;

use App\Models\Pelatihan;
use App\Models\Staff;
use Illuminate\Http\Request;

class PelatihanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pelatihan::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('id_staf', 'like', '%' . $searchTerm . '%')
                ->orWhere('judul_pelatihan', 'like', '%' . $searchTerm . '%')
                ->orWhere('tahun', 'like', '%' . $searchTerm . '%');
        }
        $pelatihan = $query->paginate(10);
        $staffs = Staff::all();

        return view('pages.staff.pelatihan', compact('pelatihan', 'staffs'));
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
            'id_staf' => 'required',
            'judul_pelatihan' => 'required',
            'file_dokumen' => 'mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $id = $request->input('id_staf');
        $dokumen = $request->file('file_dokumen');
        $nama_dok = 'pelatihan_' . $id . '.' . $dokumen->getClientOriginalExtension();
        $dokumen->move('dokumen/', $nama_dok);

        Pelatihan::create([
            'id_staf' => $request->id_staf,
            'judul_pelatihan' => $request->judul_pelatihan,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Data Pelatihan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pelatihan $pelatihan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pelatihan $pelatihan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pelatihan $pelatihan)
    {
        $request->validate([
            'id_staf' => 'required',
            'judul_pelatihan' => 'required',
            'file_dokumen' => 'nullable|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $nama_dok = $pelatihan->file_dokumen;

        if ($request->hasFile('file_dokumen')) {
            $id = $request->input('id_staf');
            $dokumen = $request->file('file_dokumen');
            $nama_dok = 'pelatihan_' . $id . '.' . $dokumen->getClientOriginalExtension();

            $file_lama = public_path('dokumen/' . $pelatihan->file_dokumen);
            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
            $dokumen->move(public_path('dokumen'), $nama_dok);
        }

        $pelatihan->update([
            'id_staf' => $request->id_staf,
            'judul_pelatihan' => $request->judul_pelatihan,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'skp berhasil diedit.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pelatihan = Pelatihan::findOrFail($id);
        $file_lama = public_path('dokumen/' . $pelatihan->file_dokumen);
        if (file_exists($file_lama)) {
            unlink($file_lama);
        }
        $pelatihan->delete();
        return redirect()->back()->with('success', 'Pelatihan berhasil dihapus.');
    }
}
