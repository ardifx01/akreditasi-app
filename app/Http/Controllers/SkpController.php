<?php

namespace App\Http\Controllers;

use App\Models\Skp;
use Illuminate\Http\Request;

class SkpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $skp = Skp::paginate(10);
        return view('pages.staff.skp', compact('skp'));
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
            'judul_skp' => 'required',
            'file_dokumen' => 'mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $id = $request->input('id_staf');
        $dokumen = $request->file('file_dokumen');
        $nama_dok = 'skp_'.$id.'.'.$dokumen->getClientOriginalExtension();
        $dokumen->move('dokumen/', $nama_dok);

        Skp::create([
            'id_staf' => $request->id_staf,
            'judul_skp' => $request->judul_skp,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Ijazah berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Skp $skp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Skp $skp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skp $skp)
    {
        $request->validate([
            'id_staf' => 'required',
            'judul_skp' => 'required',
            'file_dokumen' => 'nullable|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $nama_dok = $skp->file_dokumen;

        if ($request->hasFile('file_dokumen')) {
            $id = $request->input('id_staf');
            $dokumen = $request->file('file_dokumen');
            $nama_dok = 'skp_' . $id . '.' . $dokumen->getClientOriginalExtension();

            $file_lama = public_path('dokumen/' . $skp->file_dokumen);
            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
            $dokumen->move(public_path('dokumen'), $nama_dok);
        }

        $skp->update([
            'id_staf' => $request->id_staf,
            'judul_skp' => $request->judul_skp,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'skp berhasil diedit.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skp $skp)
    {
        $skp->delete();
        return redirect()->back()->with('success', 'SKP berhasil dihapus.');
    }
}
