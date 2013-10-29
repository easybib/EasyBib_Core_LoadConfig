#!/usr/bin/env php
<?php
ini_set('date.timezone', 'Europe/Berlin');

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version = '0.5.1';
$api_state = 'alpha';

$release_version = '0.5.1';
$release_state = 'alpha';
$release_notes = "load($path = null) now has this default.";

$summary = "A configuration loader for Zend Framework (1).";

$description = "See README.md!";

$package = new PEAR_PackageFileManager2();

$package->setOptions(
    array(
        'filelistgenerator' => 'file',
        'simpleoutput' => true,
        'baseinstalldir' => '/',
        'packagedirectory' => './',
        'dir_roles' => array(
            'src' => 'php',
            'tests' => 'test',
            'docs' => 'doc',
        ),
        'exceptions' => array(
            'README.md' => 'doc',
        ),
        'ignore' => array(
            '.git*',
            '.idea*',
            '.vagrant',
            'Vagrantfile',
            'vendor/',
            'generate-package.php',
            'phpunit.xml',
            'composer.*',
            '*.tgz',
        )
    )
);

$package->setPackage('EasyBib_Core_LoadConfig');
$package->setSummary($summary);
$package->setDescription($description);
$package->setChannel('easybib.github.com/pear');
$package->setPackageType('php');
$package->setLicense('New BSD License', 'http://www.opensource.org/licenses/bsd-license.php');

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer('lead', 'till', 'Till Klampaeckel', 'till@lagged.biz');

/**
 * Generate the list of files in {@link $GLOBALS['files']}
 *
 * @param string $path
 *
 * @return void
 */
function readDirectory($path)
{
    foreach (glob($path . '/*') as $file) {
        if (!is_dir($file)) {
            $GLOBALS['files'][] = $file;
        } else {
            readDirectory($file);
        }
    }
}
$files = array();
readDirectory(__DIR__ . '/src');

/**
 * @desc Strip this from the filename for 'addInstallAs'
 */
$base = __DIR__ . '/';

foreach ($files as $file) {

    $file2 = str_replace($base, '', $file);

    $package->addReplacement($file2, 'package-info', '@package_version@', 'version');
    $file2 = str_replace($base, '', $file);
    $package->addInstallAs($file2, str_replace('src/', '', $file2));
}

$package->setPhpDep('5.3.0');

$package->addPackageDepWithChannel('optional', 'ZF', 'pear.zfcampus.org', '1.11.11');

$package->addExtensionDep('required', 'spl');
$package->addExtensionDep('required', 'apc');
$package->setPearInstallerDep('1.9.4');
$package->generateContents();

if (array_key_exists('make', $_GET)
    || (array_key_exists('argv', $_SERVER)
    && array_key_exists(1, $_SERVER['argv'])
    && $_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
