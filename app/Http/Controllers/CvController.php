<?php

namespace App\Http\Controllers;

use App\DTOs\CvUploadData;
use App\Http\Requests\Cvs\StoreCvRequest;
use App\Models\Cv;
use App\Models\User;
use App\Services\CvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CvController extends Controller
{
    public function __construct(
        protected CvService $cvService,
    ) {}

    /**
     * Display the CV management page.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $cvs = $this->cvService->getForUser($user)->map(function (Cv $cv) use ($user) {
            return [
                'id' => $cv->id,
                'name' => $cv->name,
                'file_path' => $cv->file_path,
                'file_url' => $cv->file_path ? Storage::disk('public')->url($cv->file_path) : null,
                'is_default' => $user->default_cv_id === $cv->id,
                'created_at' => $cv->created_at?->toFormattedDateString(),
            ];
        });

        return Inertia::render('cvs/Index', [
            'cvs' => $cvs,
            'defaultCvId' => $user->default_cv_id,
        ]);
    }

    /**
     * Store a newly uploaded CV.
     */
    public function store(StoreCvRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = CvUploadData::fromRequest($request);

        $this->cvService->uploadForUser($user, $dto);

        return back()->with('success', 'CV uploaded successfully.');
    }

    /**
     * Set a CV as the user's default.
     */
    public function setDefault(Request $request, Cv $cv): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->cvService->setDefault($user, $cv);

        return back()->with('success', 'Default CV updated.');
    }

    /**
     * Delete a CV.
     */
    public function destroy(Request $request, Cv $cv): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->cvService->delete($user, $cv);

        return back()->with('success', 'CV deleted.');
    }
}
