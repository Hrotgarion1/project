<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\Media;
use App\Models\TypeA;
use App\Models\TypeB;
use App\Models\TypeC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{

    public function index($mediableType, $identityId)
    {
        try {
            $modelClass = match ($mediableType) {
                'identity' => Identity::class,
                'type-a' => TypeA::class,
                'type-b' => TypeB::class,
                'type-c' => TypeC::class,
                default => throw new \Exception('Invalid mediable type'),
            };

            $identity = Identity::where('id', $identityId)->firstOrFail();
            $this->authorize('view', $identity);

            $media = Media::where('mediable_type', $modelClass)
                ->where('mediable_id', $identityId)
                ->orderBy('position')
                ->get();

            return response()->json(['media' => $media]);
        } catch (\Exception $e) {
            Log::error('Error in MediaController::index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }
    
   public function store(Request $request, $mediableType, $slug)
    {
        Log::info('MediaController::store called', [
            'mediableType' => $mediableType,
            'slug' => $slug,
            'request' => $request->all(),
            'file_size' => $request->file('file') ? $request->file('file')->getSize() : null,
        ]);

        try {
            $modelClass = match ($mediableType) {
                'identity' => Identity::class,
                'type-a' => TypeA::class,
                'type-b' => TypeB::class,
                'type-c' => TypeC::class,
                default => throw new \Exception('Invalid mediable type'),
            };

            $identity = Identity::where('slug', $slug)->firstOrFail();
            $id = $identity->id;

            if (in_array($modelClass, [TypeA::class, TypeB::class, TypeC::class])) {
                $record = new $modelClass(['id' => $id]);
            } else {
                $record = $modelClass::find($id);
                if (!$record) {
                    Log::error('Record not found', ['mediableType' => $mediableType, 'id' => $id]);
                    return response()->json(['error' => 'record_not_found'], 404);
                }
            }

            $this->authorize('upload-media', $record);

            $mediaData = [
                'folder' => $request->input('folder', $mediableType),
                'position' => Media::where('mediable_type', $modelClass)
                    ->where('mediable_id', $id)
                    ->max('position') + 1 ?? 1,
                'mediable_id' => $id,
                'mediable_type' => $modelClass,
                'role' => $request->input('role', 'gallery'),
            ];

            if ($request->has('youtube_id')) {
                $request->validate([
                    'youtube_id' => 'required|string|size:11',
                    'folder' => 'nullable|string|max:255',
                    'role' => 'nullable|string|in:logo,header,gallery,youtube',
                ]);

                $mediaData = array_merge($mediaData, [
                    'file_path' => "https://www.youtube.com/watch?v={$request->youtube_id}",
                    'file_name' => "YouTube Video {$request->youtube_id}",
                    'file_type' => 'video/youtube',
                    'is_youtube' => true,
                    'youtube_id' => $request->youtube_id,
                ]);
            } else {
                $request->validate([
                    'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480', // Aumentado a 20 MB
                    'folder' => 'nullable|string|max:255',
                    'role' => 'nullable|string|in:logo,header,gallery,youtube',
                ]);

                $file = $request->file('file');
                $path = $file->store("media/{$mediableType}/{$mediaData['folder']}", 'public');

                $mediaData = array_merge($mediaData, [
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'is_youtube' => false,
                    'youtube_id' => null,
                ]);
            }

            $media = Media::create($mediaData);

            Log::info('Media created', ['media_id' => $media->id]);

            return response()->json(['media' => $media->fresh()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'validation_error', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error in MediaController::store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    public function destroy($mediableType, $slug, $mediaId)
    {
        try {
            $modelClass = match ($mediableType) {
                'identity' => Identity::class,
                'type-a' => TypeA::class,
                'type-b' => TypeB::class,
                'type-c' => TypeC::class,
                default => throw new \Exception('Invalid mediable type'),
            };

            // Buscar identity_id por slug
            $identity = Identity::where('slug', $slug)->firstOrFail();
            $id = $identity->id;

            $media = Media::where('mediable_type', $modelClass)
                ->where('mediable_id', $id)
                ->where('id', $mediaId)
                ->firstOrFail();

            $record = in_array($modelClass, [TypeA::class, TypeB::class, TypeC::class])
                ? new $modelClass(['id' => $id])
                : $media->mediable;

            $this->authorize('delete-media', $record);

            if (!$media->is_youtube) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in MediaController::destroy', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    public function reorder(Request $request, $mediableType, $slug)
    {
        try {
            $request->validate(['order' => 'required|array']);

            $modelClass = match ($mediableType) {
                'identity' => Identity::class,
                'type-a' => TypeA::class,
                'type-b' => TypeB::class,
                'type-c' => TypeC::class,
                default => throw new \Exception('Invalid mediable type'),
            };

            // Buscar identity_id por slug
            $identity = Identity::where('slug', $slug)->firstOrFail();
            $id = $identity->id;

            $record = in_array($modelClass, [TypeA::class, TypeB::class, TypeC::class])
                ? new $modelClass(['id' => $id])
                : $modelClass::find($id);

            if (!$record && !in_array($modelClass, [TypeA::class, TypeB::class, TypeC::class])) {
                Log::error('Record not found', ['mediableType' => $mediableType, 'id' => $id]);
                return response()->json(['error' => 'record_not_found'], 404);
            }

            $this->authorize('reorder-media', $record);

            foreach ($request->order as $item) {
                Media::where('id', $item['id'])
                    ->where('mediable_type', $modelClass)
                    ->where('mediable_id', $id)
                    ->update(['position' => $item['position']]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in MediaController::reorder', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
