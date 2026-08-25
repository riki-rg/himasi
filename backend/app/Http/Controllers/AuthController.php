<?php

namespace App\Http\Controllers;

use App\Models\Komunitas;
use App\Models\KomunitasMember;
use App\Models\Member;
use App\Models\User;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * POST /auth/register — registrasi mandiri (open registration).
     * Akun berstatus pending menunggu approval admin pusat (ADR D6).
     * Bila `komunitas` diisi, sekaligus mengajukan keanggotaan dalam satu transaksi.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $this->validateRegistration($request);

        $member = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => 'pending',
            ]);

            $member = Member::query()->create([
                'user_id' => $user->id,
                'nim' => $data['nim'],
                'nama' => $data['nama'],
                'prodi' => $data['prodi'] ?? null,
                'angkatan' => $data['angkatan'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'link_portofolio' => $data['link_portofolio'] ?? null,
                'link_instagram' => $data['link_instagram'] ?? null,
                'status' => 'aktif',
            ]);

            if (! empty($data['komunitas'])) {
                KomunitasMember::query()->create([
                    'member_id' => $member->id,
                    'komunitas_id' => Komunitas::idByKode($data['komunitas']),
                    'status' => 'pending',
                ]);
            }

            return $member;
        });

        return response()->json($this->memberResource($member), Response::HTTP_CREATED);
    }

    /**
     * POST /auth/login — terbitkan token Sanctum.
     * 401 kredensial salah · 423 akun masih pending.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            throw Problem::unauthorized('Email atau password salah.');
        }

        if ($user->status === 'pending') {
            throw Problem::accountPending();
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /** POST /auth/logout — cabut token saat ini. */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /** GET /auth/me — profil + role & komunitas. */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $komunitas = [];
        if ($user->member !== null) {
            $komunitas = $user->member->keanggotaanKomunitas()
                ->where('status', 'disetujui')
                ->with('komunitas')
                ->get()
                ->map(fn ($km) => [
                    'kode' => $km->komunitas->kode,
                    'nama' => $km->komunitas->nama,
                ])
                ->all();
        }

        $penugasanAktif = $user->member?->penugasans()
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
            ->with(['jabatan.divisi', 'periode'])
            ->get()
            ->map(fn ($p) => [
                'jabatan' => $p->jabatan->nama,
                'divisi' => $p->jabatan->divisi->nama,
                'periode' => $p->periode->nama,
            ])
            ->all() ?? [];

        return response()->json([
            ...$this->userResource($user),
            'member' => $user->member !== null ? $this->memberResource($user->member) : null,
            'komunitas' => $komunitas,
            'penugasan_aktif' => $penugasanAktif,
        ]);
    }

    /** PATCH /auth/me — update profil sendiri (multipart, foto ≤5MB). */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $member = $user->member;

        $data = $request->validate([
            'nama' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'prodi' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'link_portofolio' => ['nullable', 'url'],
            'link_instagram' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ]);

        if (isset($data['foto'])) {
            $path = $data['foto']->store('foto-profil', 'public');
            $data['foto_path'] = $path;
        }
        unset($data['foto']);

        DB::transaction(function () use ($user, $member, $data) {
            $userFillable = array_intersect_key($data, array_flip(['name', 'email']));
            if ($userFillable !== []) {
                $user->update($userFillable);
            }

            if ($member !== null && ($memberData = collect($data)->except(['name', 'email'])->all()) !== []) {
                $member->update($memberData);
            }
        });

        return response()->json($this->memberResource($member->refresh()));
    }

    /** PUT /auth/password — ganti password akun login saat ini. */
    public function updatePassword(Request $request): Response
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8'],
        ]);

        if (! Hash::check($data['password_lama'], $request->user()->password)) {
            throw Problem::validation(
                ['password_lama' => ['Password lama tidak sesuai.']],
                'Password lama tidak sesuai.'
            );
        }

        $request->user()->update(['password' => $data['password_baru']]);

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRegistration(Request $request): array
    {
        return $request->validate([
            'nim' => ['required', 'string', 'max:20', Rule::unique('members', 'nim')],
            'nama' => ['required', 'string', 'max:255'],
            'prodi' => ['nullable', 'string', 'max:255'],
            'angkatan' => ['required', 'digits:4', 'integer'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'link_portofolio' => ['nullable', 'url'],
            'link_instagram' => ['nullable', 'string', 'max:255'],
            'komunitas' => ['nullable', Rule::in(['HIMSI', 'BITSI', 'SIBINER'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberResource(Member $member): array
    {
        return [
            'id' => $member->id,
            'nim' => $member->nim,
            'nama' => $member->nama,
            'prodi' => $member->prodi,
            'angkatan' => (int) $member->angkatan,
            'email' => $member->email,
            'no_hp' => $member->no_hp,
            'alamat' => $member->alamat,
            'foto_path' => $member->foto_path,
            'link_portofolio' => $member->link_portofolio,
            'link_instagram' => $member->link_instagram,
            'status' => $member->status,
            'created_at' => $member->created_at?->toISOString(),
        ];
    }
}
