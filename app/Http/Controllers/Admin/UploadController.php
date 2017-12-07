<?php

namespace App\Http\Controllers\Admin;

use App\Components\Upload\UploadManager;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Http\Request;
use Exception;
use App\Http\Requests\UploadNewFolderRequest;
use Illuminate\Support\Facades\File;
use Log;

class UploadController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $phpRepository = new PhpRepository();
        $this->manager = new UploadManager($phpRepository);
    }

    /**
     * 创建新目录
     */
    public function createFolder(UploadNewFolderRequest $request)
    {
        $new_folder = $request->get('new_folder');
        $folder = $request->get('folder').'/'.$new_folder;

        $result = $this->manager->createDirectory($folder);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess("Folder '$new_folder' created.");
        }

        $error = $result ? : "An error occurred creating directory.";
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * 删除文件
     */
    public function deleteFile(Request $request)
    {
        $del_file = $request->get('del_file');
        $path = $request->get('folder').'/'.$del_file;

        $result = $this->manager->deleteFile($path);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess("File '$del_file' deleted.");
        }

        $error = $result ? : "An error occurred deleting file.";
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * 删除目录
     */
    public function deleteFolder(Request $request)
    {
        $del_folder = $request->get('del_folder');
        $folder = $request->get('folder').'/'.$del_folder;

        $result = $this->manager->deleteDirectory($folder);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess("Folder '$del_folder' deleted.");
        }

        $error = $result ? : "An error occurred deleting directory.";
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * 上传文件
     */
    public function uploadFile(Request $request)
    {
        $file = $_FILES['file'];
        $fileName = $request->get('file_name');
        $fileName = $fileName ?: $file['name'];
        $path = $fileName;
        $content = File::get($file['tmp_name']);

        try {
            if (!in_array($_FILES['file']['type'], ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'])) {
                throw new Exception('图片格式错误, 支持jpeg, png, gif', 12);
            }

            Log::info('===========', ['file' => $file]);
            if ($_FILES["file"]["size"] > 1000000) {
                throw new Exception('文件大小不超过4M', 13);
            }
            $result = $this->manager->saveFile($path, $content);
            if ($result != true) {
                throw new Exception('上传文件错误', 11);
            }
            $rs['url'] = '/uploads/'.$path;
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess($rs);
    }
}