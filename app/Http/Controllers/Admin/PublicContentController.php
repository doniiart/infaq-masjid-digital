<?php

namespace App\Http\Controllers\Admin;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\DevelopmentMedia;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicContentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/PublicContent/Index', [
            'profile' => [
                'tagline' => SiteSetting::getValue(
                    'profile_tagline',
                    "Pusat ibadah, pendidikan Al-Qur'an, dan pemberdayaan ekonomi umat."
                ),
                'history' => SiteSetting::getValue(
                    'profile_history',
                    'Masjid At Taqwa didirikan atas swadaya masyarakat dan wakaf tanah warga setempat sebagai sarana ibadah berjamaah serta pusat pembinaan keislaman umat. Seiring bertambahnya jumlah jamaah, takmir bersama panitia pembangunan menginisiasi perluasan ruang ibadah dan menerapkan pencatatan infaq berbasis QRIS digital agar pengelolaan dana umat dapat diaudit secara terbuka.'
                ),
                'committee_members' => SiteSetting::getCommitteeMembers(),
            ],
            'mediaList' => DevelopmentMedia::query()
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tagline'                    => ['required', 'string', 'max:255'],
            'history'                    => ['required', 'string', 'max:5000'],
            'committee_members'          => ['required', 'array', 'min:1'],
            'committee_members.*.role'   => ['required', 'string', 'max:120'],
            'committee_members.*.name'   => ['required', 'string', 'max:150'],
        ]);

        SiteSetting::setValue('profile_tagline', $validated['tagline']);
        SiteSetting::setValue('profile_history', $validated['history']);
        SiteSetting::setValue('committee_members', array_values($validated['committee_members']));

        AuditLogger::log('PUBLIC_PROFILE_UPDATED', null, null, [
            'committee_count' => count($validated['committee_members']),
        ]);

        return back()->with('success', 'Profil masjid dan struktur panitia berhasil diperbarui.');
    }

    public function storeMedia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:180'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'media_type'   => ['required', 'in:image,video,youtube'],
            'file'         => [
                'required_if:media_type,image,video',
                'nullable',
                'file',
                'max:51200', // Maksimal 50 MB
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
            ],
            'external_url' => ['required_if:media_type,youtube', 'nullable', 'url', 'max:500'],
        ], [
            'file.required_if'         => 'Silakan pilih file foto atau video yang akan diunggah.',
            'file.max'                 => 'Ukuran file maksimal 50 MB.',
            'file.mimes'               => 'Format file harus JPG, PNG, WebP, MP4, WebM, atau MOV.',
            'external_url.required_if' => 'Masukkan tautan video YouTube.',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $folder = $validated['media_type'] === 'video' ? 'development/videos' : 'development/photos';
            $filePath = $request->file('file')->store($folder, 'public');
        }

        $media = DevelopmentMedia::query()->create([
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'media_type'   => $validated['media_type'],
            'file_path'    => $filePath,
            'external_url' => $validated['media_type'] === 'youtube' ? $validated['external_url'] : null,
            'is_published' => true,
            'uploaded_by'  => $request->user()?->id,
        ]);

        AuditLogger::log('DEVELOPMENT_MEDIA_CREATED', DevelopmentMedia::class, $media->id, [
            'title'      => $media->title,
            'media_type' => $media->media_type,
        ]);

        return back()->with('success', 'Dokumentasi progress pembangunan berhasil ditambahkan.');
    }

    public function destroyMedia(DevelopmentMedia $media): RedirectResponse
    {
        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $title = $media->title;
        $id = $media->id;
        $media->delete();

        AuditLogger::log('DEVELOPMENT_MEDIA_DELETED', DevelopmentMedia::class, $id, [
            'title' => $title,
        ]);

        return back()->with('success', 'Dokumentasi progress pembangunan berhasil dihapus.');
    }
}