<?php

namespace App\Http\Controllers;

use App\Models\Sertifikasi;
use App\Models\Staff;
use Illuminate\Http\Request;

class SertifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sertifikasi::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('id_staf', 'like', '%' . $searchTerm . '%')
                ->orWhere('judul_sertifikasi', 'like', '%' . $searchTerm . '%')
                ->orWhere('tahun', 'like', '%' . $searchTerm . '%');
        }
        $sertifikasi = $query->paginate(10);
        $staffs = Staff::all();
        return view('pages.staff.sertifikasi', compact('sertifikasi', 'staffs'));
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
            'judul_sertifikasi' => 'required',
            'file_dokumen' => 'mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $id = $request->input('id_staf');
        $dokumen = $request->file('file_dokumen');
        $nama_dok = 'sertifikasi_' . $id . '.' . $dokumen->getClientOriginalExtension();
        $dokumen->move('dokumen/', $nama_dok);

        Sertifikasi::create([
            'id_staf' => $request->id_staf,
            'judul_sertifikasi' => $request->judul_sertifikasi,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Ijazah berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sertifikasi $sertifikasi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sertifikasi $sertifikasi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sertifikasi $sertifikasi)
    {
        $request->validate([
            'id_staf' => 'required',
            'judul_sertifikasi' => 'required',
            'file_dokumen' => 'nullable|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $nama_dok = $sertifikasi->file_dokumen;

        if ($request->hasFile('file_dokumen')) {
            $id = $request->input('id_staf');
            $dokumen = $request->file('file_dokumen');
            $nama_dok = 'sertifikasi_' . $id . '.' . $dokumen->getClientOriginalExtension();

            $file_lama = public_path('dokumen/' . $sertifikasi->file_dokumen);
            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
            $dokumen->move(public_path('dokumen'), $nama_dok);
        }

        $sertifikasi->update([
            'id_staf' => $request->id_staf,
            'judul_sertifikasi' => $request->judul_sertifikasi,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Sertifikasi berhasil diedit.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // saat data dihapus maka file yang ada di folder 'public/dokumen' juga terhapus sesuai dengan id yang dihapus
        $sertifikasi = Sertifikasi::findOrFail($id);
        $file_lama = public_path('dokumen/' . $sertifikasi->file_dokumen);
        if (file_exists($file_lama)) {
            unlink($file_lama);
        }
        $sertifikasi->delete();
        return redirect()->back()->with('success', 'Sertifikasi berhasil dihapus.');
    }
}
