<?php

namespace App\Http\Controllers;

use App\Models\Transkrip;
use App\Models\Staff;
use Illuminate\Http\Request;

class TranskripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transkrip = Transkrip::paginate(10);
        $staffs = Staff::all();
        return view('pages.staff.transkrip', compact('transkrip','staffs'));
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
            'judul_transkrip' => 'required',
            'file_dokumen' => 'mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $id = $request->input('id_staf');
        $dokumen = $request->file('file_dokumen');
        $nama_dok = 'transkrip_'.$id.'.'.$dokumen->getClientOriginalExtension();
        $dokumen->move('dokumen/', $nama_dok);

        Transkrip::create([
            'id_staf' => $request->id_staf,
            'judul_transkrip' => $request->judul_transkrip,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Ijazah berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transkrip $transkrip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transkrip $transkrip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transkrip $transkrip)
    {
        $request->validate([
            'id_staf' => 'required',
            'judul_transkrip' => 'required',
            'file_dokumen' => 'nullable|mimes:pdf',
            'tahun' => 'required|integer'
        ]);

        $nama_dok = $transkrip->file_dokumen;

        if ($request->hasFile('file_dokumen')) {
            $id = $request->input('id_staf');
            $dokumen = $request->file('file_dokumen');
            $nama_dok = 'transkrip_' . $id . '.' . $dokumen->getClientOriginalExtension();

            $file_lama = public_path('dokumen/' . $transkrip->file_dokumen);
            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
            $dokumen->move(public_path('dokumen'), $nama_dok);
        }

        $transkrip->update([
            'id_staf' => $request->id_staf,
            'judul_transkrip' => $request->judul_transkrip,
            'file_dokumen' => $nama_dok,
            'tahun' => $request->tahun
        ]);

        return redirect()->back()->with('success', 'Transkrip berhasil diedit.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // saat data dihapus maka file yang ada di folder 'public/dokumen' juga terhapus sesuai dengan id yang dihapus
        $transkrip = Transkrip::findOrFail($id);
        $file_lama = public_path('dokumen/' . $transkrip->file_dokumen);
        if (file_exists($file_lama)) {
            unlink($file_lama);
        }
        $transkrip->delete();
        return redirect()->back()->with('success', 'transkrip berhasil dihapus.');
    }
}
