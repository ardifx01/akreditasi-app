@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
    <div class="d-flex mt-5 align-items-center justify-content-center max-vh-100 bg-gradient"
        style="background: linear-gradient(135deg, #f8fafc 60%, #e0e7ff 100%);">
        <div class="text-center animate__animated animate__bounceIn">
            <img src="https://media.tenor.com/2uyENRmiUt0AAAAC/cat-computer.gif" alt="404 Cat" style="max-width:220px;"
                class="mb-4">
            <h1 class="display-3 fw-bold mb-2 text-indigo">404</h1>
            <h4 class="mb-3 text-secondary">Oops! Halaman tidak ditemukan.</h4>
            <a href="{{ url('/') }}" class="btn btn-lg" style="background: #6366f1; color: #fff;"><i
                    class="fas fa-home me-2"></i>Kembali ke Beranda</a>
            <div class="mt-4">
                <span class="text-muted">Atau coba refresh</span>
            </div>
        </div>
    </div>
@endsection
