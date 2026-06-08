# Backend / Uploads & Media

- Temporary uploads: `App\Models\TemporaryUpload` with ULID PK (`$incrementing = false`, `$keyType = 'string'`), factory, fillable `id,user_id,disk,path,original_name,mime_type,size`.
- Routes: `POST /temporary-uploads` and `DELETE /temporary-uploads/{temporaryUpload}` in `routes/web.php`.
- Controller: `TemporaryUploadController` (store/destroy).
- Actions: `StoreTemporaryUpload`, `PromoteTemporaryUpload`, `DeleteTemporaryUpload` under `app/Actions/Uploads/`.
- Console command: `PruneTemporaryUploads` for cleaning old temporary uploads.
- Spatie Media Library: `User` implements `HasMedia` via `InteractsWithMedia`; registers `avatar` collection (`singleFile`, accepts `image/jpeg`, `image/png`, `image/webp`, `image/svg+xml`).
- Profile avatar upload/removal in `Settings\ProfileController` using media library.
- Branding assets resolved by `App\Support\Branding\BrandingAssetResolver` from `StyleSettings` paths; falls back to `public/assets/images/*`.
