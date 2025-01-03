<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    // Menampilkan semua movie
    public function index()
    {
        // Mengambil semua movie dengan genre terkait
        $movies = Movie::with('genre')->latest()->paginate(10); // Ditambah paginasi
        return view('movies.index', compact('movies'));
    }

    // Menampilkan form untuk menambah movie baru
    public function create()
    {
        // Mengambil semua genre dari database
        $genres = Genre::all();
        return view('movies.create', compact('genres'));
    }

    // Menyimpan data movie baru ke database
    public function store(Request $request)
{
    // Validasi data
    $validated = $request->validate([
        'title' => 'required|string|max:255|unique:movies,title', // Pastikan judul unik
        'synopsis' => 'required|string|min:10', // Minimal 10 karakter untuk sinopsis
        'poster' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:10240', // Validasi ekstensi dan ukuran file
        'year' => 'required|integer|min:1800|max:' . date('Y'),
        'available' => 'nullable|boolean', // Validasi nullable dan boolean
        'genre_id' => 'required|exists:genres,id', // Pastikan genre valid
    ]);

    // Upload file poster
    $posterPath = $request->file('poster')->store('posters', 'public'); // Folder 'posters' di storage/public

    // Simpan data ke database
    $movie = Movie::create([
        'id' => Str::uuid(),
        'title' => $validated['title'],
        'synopsis' => $validated['synopsis'],
        'poster' => $posterPath, // Simpan path poster
        'year' => $validated['year'],
        'available' => $validated['available'] ?? true, // Set default `true` jika `available` tidak diisi
        'genre_id' => $validated['genre_id'],
    ]);

    // Redirect dengan pesan sukses
    return redirect()->route('movies.index')->with('success', 'Movie created successfully.');
}

    // Menampilkan detail movie berdasarkan ID
    public function show($id)
    {
        // Cari movie berdasarkan ID
        $movie = Movie::with('genre')->findOrFail($id);
        return view('movies.show', compact('movie'));
    }
}
