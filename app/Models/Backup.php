<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Backup extends Model
{
    use HasFactory;

    protected $table = 'backups';
    protected $fillable = ['file_name', 'file_path', 'created_at'];
    public $timestamps = false;

    protected $casts = [
        'file_name' => 'string',
        'file_path' => 'string',
    ];

    // Accessor to get decrypted file_name with fallback
    public function getDecryptedFileNameAttribute()
    {
        try {
            // Check if the value looks like it's encrypted
            if ($this->file_name && strlen($this->file_name) > 32) {
                return Crypt::decryptString($this->file_name);
            }
            // If it doesn't look encrypted or decryption fails, return original
            return $this->file_name;
        } catch (\Exception $e) {
            \Log::warning('Error decrypting filename, returning original: ' . $e->getMessage());
            return $this->file_name;
        }
    }

    // Accessor to get decrypted file_path with fallback
    public function getDecryptedFilePathAttribute()
    {
        try {
            // Check if the value looks like it's encrypted
            if ($this->file_path && strlen($this->file_path) > 32) {
                return Crypt::decryptString($this->file_path);
            }
            // If it doesn't look encrypted or decryption fails, return original
            return $this->file_path;
        } catch (\Exception $e) {
            \Log::warning('Error decrypting file path, returning original: ' . $e->getMessage());
            return $this->file_path;
        }
    }

    // Scope to find by decrypted filename
    public function scopeWhereFileName($query, $filename)
    {
        try {
            $encryptedFilename = Crypt::encryptString($filename);
            return $query->where('file_name', $encryptedFilename);
        } catch (\Exception $e) {
            // If encryption fails, search by plain filename
            return $query->where('file_name', $filename);
        }
    }
}