@echo off
setlocal

echo ============================================
echo   NFT Thumbnail Backfill Tool
echo   Rebuilds missing/deleted thumbnails
echo ============================================
echo.

cd /d "%~dp0.."

echo Collections:
echo   0. All collections
php artisan tinker --execute="App\Models\Collection::query()->orderBy('id')->get(['id','name'])->each(function($c){echo '  '.$c->id.'. '.$c->name.PHP_EOL;});"
echo.
set /p COLLECTION_CHOICE=Type collection number (0 = all): 
if "%COLLECTION_CHOICE%"=="" set COLLECTION_CHOICE=0

echo.
echo Run mode:
echo   1. Missing only (also repairs deleted thumbnail files)
echo   2. Force re-generate all in selected scope
set /p MODE_CHOICE=Choose mode [1/2] (default 1): 
if "%MODE_CHOICE%"=="" set MODE_CHOICE=1

set "CMD=php artisan nfts:backfill-thumbnails"
if not "%COLLECTION_CHOICE%"=="0" set "CMD=%CMD% --collection-id=%COLLECTION_CHOICE%"
if "%MODE_CHOICE%"=="2" set "CMD=%CMD% --force"

if "%MODE_CHOICE%"=="2" (
  echo.
  echo Clearing existing thumbnails in selected scope...
  php artisan tinker --execute="$cid=(int)getenv('COLLECTION_CHOICE');$q=App\Models\Nft::query();if($cid>0){$q->where('collection_id',$cid);} $dirs=[];$q->get(['image_url','thumbnail_url'])->each(function($n)use(&$dirs){foreach([$n->image_url,$n->thumbnail_url] as $p){if(!is_string($p)||trim($p)===''){continue;}$p=str_replace('\\','/',$p);$p=ltrim($p,'/');$d=trim(dirname($p),'.');if($d===''){continue;}$dirs[(substr($d,-7)==='/thumbs')?$d:($d.'/thumbs')]=true;}});$deleted=0;$scanned=0;foreach(array_keys($dirs) as $d){$abs=public_path($d);if(!is_dir($abs)){continue;}$scanned++;foreach(glob($abs.'/*')?:[] as $f){if(is_file($f)){@unlink($f);$deleted++;}}}echo 'Cleared '.$deleted.' file(s) across '.$scanned.' thumbnail folder(s).'.PHP_EOL;"
)

echo.
echo Running: %CMD%
%CMD%

echo.
echo Done. Press any key to close.
pause >nul
endlocal
