# Chopper Image Attachments

GitHub Issue: #113

## Summary

Add image attachment support to Chopper so users can send images alongside messages. The AI model (Gemini 2.5 Flash) can analyze attached images. Images are persisted and displayed when loading old conversations.

## Constraints

- Images only (JPG, PNG, GIF, WebP)
- Up to 5 images per message
- Max 10MB per image
- Input methods: button, drag-and-drop, clipboard paste

## Architecture

### Storage

Store uploaded images to the `local` disk at `chopper-attachments/{uuid}.{ext}`. The `local` disk has `serve => true`, so Laravel can serve private files via route.

Pass `Image::fromStorage('chopper-attachments/...')` to the SDK. The SDK serializes a lightweight JSON reference in the `attachments` column:

```json
[{"type": "stored-image", "path": "chopper-attachments/abc.jpg", "disk": "local"}]
```

No base64 bloat in the database. Images served from disk.

### Livewire Component (`Chopper.php`)

Add `WithFileUploads` trait. New property:

```php
/** @var UploadedFile[] */
public array $attachments = [];
```

**`send()` method changes:**
1. Validate attachments (images, max 5, max 10MB each)
2. Store each file permanently: `$file->store('chopper-attachments', 'local')`
3. Collect storage paths
4. Clear `$attachments`
5. Add message to `$messages` with `attachments` key containing storage paths
6. Pass storage paths to `ask()`

**`ask()` method changes:**
- Accept storage paths parameter
- Build `Image::fromStorage($path)` for each path
- Pass attachments array to `$agent->stream($message, attachments: $images)`

**`loadMessages()` changes:**
- Also read `attachments` JSON column from DB
- Parse stored-image entries, extract `path` values
- Include in `$messages` array for rendering

### UI (`chopper.blade.php`)

Replace `<flux:input>` + `<flux:button>` form with `<flux:composer>`:

```blade
<flux:composer wire:model="message" wire:submit="send">
    <x-slot:header>
        {{-- Staged image previews with remove buttons --}}
    </x-slot:header>

    <x-slot:actionsLeading>
        <flux:button icon="photo" size="sm" variant="subtle" />
        <input type="file" wire:model="attachments" multiple accept="image/*" hidden />
    </x-slot:actionsLeading>

    <x-slot:actionsTrailing>
        <flux:button icon="paper-airplane" size="sm" variant="primary" type="submit" />
    </x-slot:actionsTrailing>
</flux:composer>
```

**Alpine.js additions:**
- Drag-and-drop: intercept `dragover`/`drop` events, feed files to Livewire's file upload
- Clipboard paste: intercept `paste` event, extract image items, feed to Livewire
- Preview thumbnails: `URL.createObjectURL()` for staged files before upload

**Message display changes:**
- User messages with attachments render thumbnail images (clickable to view full size)
- Images served via `Storage::disk('local')->url($path)` or route-based serving

### Data Flow

```
User selects/drops/pastes images
  -> Livewire WithFileUploads uploads to temp storage
  -> Thumbnails shown in header slot
  -> User clicks send
  -> send() stores images permanently to local disk
  -> send() passes storage paths + message to ask()
  -> ask() creates Image::fromStorage() instances
  -> ask() calls $agent->stream($message, attachments: [...])
  -> SDK sends images to Gemini, streams response
  -> SDK persists message with attachment path references in DB
  -> Response streamed back to UI
```

### Testing

- Uploading images stores them to disk
- `stream()` receives attachments parameter (via `ChopperAgent::fake()`)
- Loading old conversations with attachments displays images
- Validation: only images accepted, max 5 files, max 10MB
- Sending without attachments still works as before
