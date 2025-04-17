<?php

namespace App\Http\Controllers;

use App\Models\Pelatihan;
use Illuminate\Http\Request;

class PelatihanController extends Controller
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
            'id_staff' => 'required|min:5',
            'judul_pelatihan' => 'required|min:5',
            'file_dokumen' => 'required|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        // penyimpanan file
        $nama = strtolower(str_replace(' ', '_', $request->id_staf));
        $fileName = $nama . '.pdf';
        $path = $request->file('file')->storeAs('pdfs', $fileName, 'public');

        Pelatihan::create([
            'id_staff' => $request->id_staf,
            'judul_pelatihan' => $request->judul_pelatihan,
            'file_dokumen' => $path,
            'tahun' => $request->tahun
        ]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pelatihan $pelatihan)
    {
        //
    }
}
