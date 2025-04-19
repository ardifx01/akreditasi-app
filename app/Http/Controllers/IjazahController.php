<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Ijazah;
use Illuminate\Http\Request;

class IjazahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ijazah = Ijazah::paginate(10);
        return view('pages.staff.ijazah', compact('ijazah'));
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
            'judul_ijazah' => 'required',
            'file_dokumen' => 'required',
            'tahun' => 'required|integer'
        ]);
        Ijazah::create([
            'id_staf' => $request->id_staf,
            'judul_ijazah' => $request->judul_ijazah,
            'file_dokumen' => $request->file_dokumen,
            'tahun' => $request->tahun
        ]);
        return redirect()->back()->with('success', 'Ijazah berhasil ditambahkan.');
        // $request->validate([
        //     // 'id_staf' => 'required|min:5|exists:mysql2.tb_staff,id_staf',
        //     'id_staf' => 'required|min:5',
        //     'judul_ijazah' => 'required|min:5',
        //     'file_dokumen' => 'required|min:5',
        //     'tahun' => 'required|max:4'
        // ]);

        // // penyimpanan file
        // // $nama = strtolower(str_replace(' ', '_', $request->id_staf));
        // // $fileName = $nama . '.pdf';
        // // $path = $request->file('file_dokumen')->storeAs('pdfs', $fileName, 'public');

        // Ijazah::create([
        //     'id_staf' => $request->id_staf,
        //     'judul_ijazah' => $request->judul_ijazah,
        //     'file_dokumen' => $request->file_dokumen,
        //     'tahun' => $request->tahun
        // ]);

        // return redirect()->back()->with('success', 'Ijazah berhasil ditambahkan.');
        // dd($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Ijazah $ijazah)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ijazah $ijazah)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ijazah $ijazah)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ijazah $ijazah)
    {
        //
    }

    public function testkoneksi(){
        $staffs = DB::connection('mysql2')->table('tb_staff')->get();
        dd($staffs);
    }
}
