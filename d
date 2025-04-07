[33mcommit f267e1b0de79176e908b84432501a8c9e213b624[m[33m ([m[1;33mtag: v2.1.0[m[33m)[m
Author: FARMAPPS-MUIZ\farmapps <farmapps.muiz@gmail.com>
Date:   Fri Mar 21 08:39:01 2025 +0800

    fix: increase memory limit

[1mdiff --git a/src/Helpers/GoogleDriveHelper.php b/src/Helpers/GoogleDriveHelper.php[m
[1mindex 0091364..dc4f50f 100644[m
[1m--- a/src/Helpers/GoogleDriveHelper.php[m
[1m+++ b/src/Helpers/GoogleDriveHelper.php[m
[36m@@ -81,6 +81,8 @@[m [mclass GoogleDriveHelper[m
         [m
         $media->setFileSize($stream->getSize());[m
 [m
[32m+[m[32m        ini_set('memory_limit','2048M');[m
[32m+[m
         $uploadedFile = $service->files->create($file, [[m
             'data' => $stream,[m
             'mimeType' => mime_content_type($filePath),[m
