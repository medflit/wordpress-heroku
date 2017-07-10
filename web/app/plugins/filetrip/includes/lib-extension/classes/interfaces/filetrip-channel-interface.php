<?php

/*
 * This calss should provide an abstraction for handling chunked uploads
 * for both Dropbox, Google Drive, and all of the other channels.
 */


interface iFiletrip_Channel
{
    public function auth_start();
    public function is_active();
    public function deactivate();
    public function get_settings();
    public function update_settings();
    public function get_folder_list();
    public function resumable_file_upload($destination_path , $file_uri, $filesize, $userTaggedFolder = false, $username = 'Guest');
}

