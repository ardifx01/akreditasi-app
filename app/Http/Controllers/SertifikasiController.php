<?php

namespace App\Http\Controllers;

use App\Models\Sertifikasi;
use Illuminate\Http\Request;

class SertifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'judul_sertifikasi' => 'required|min:5',
            'file_dokumen' => 'required|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        // penyimpanan file
        $nama = strtolower(str_replace(' ', '_', $request->id_staf));
        $fileName = $nama . '.pdf';
        $path = $request->file('file')->storeAs('pdfs', $fileName, 'public');

        Sertifikasi::create([
            'id_staf' => $request->id_staf,
            'judul_sertifikasi' => $request->judul_pelatihan,
            'file_dokumen' => $path,
            'tahun' => $request->tahun
        ]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sertifikasi $sertifikasi)
    {
        //
    }
}
