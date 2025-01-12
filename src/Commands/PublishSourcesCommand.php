<?php
namespace RA\CLI\Commands;

use RA\CLI\Console;

class PublishSourcesCommand implements CommandInterface
{
    private static $cwd;

    public static function run($options) {
        //change cwd
        self::$cwd = getcwd().'/../sources';
        chdir(self::$cwd);

        // Checkout the base branch
        $main_branch = 'master';
        shell_exec("git checkout $main_branch");

        // Update the base branch
        shell_exec("git pull origin $main_branch");

        $folders = scandir(self::$cwd);
        $branches = [];
        foreach ( $folders as $folder ) {
            if ( in_array($folder, ['.', '..', '.git']) || !is_dir($folder) ) {
                continue;
            }

            $branches[] = $folder;
        }

        foreach ($branches as $branch) {
            //check if branch exists
            if ( !preg_match('/fatal/', shell_exec("git show-ref --verify --quiet refs/heads/$branch")) ) {
                // Branch exists; get its latest version
                $current_version = trim(shell_exec("git tag -l '*' --sort=-v:refname | head -n 1") ?? '');

                if ( !$current_version ) {
                    $current_version = 'v1.0.0';
                }

                $version = self::increment_version($current_version);
            }
            else {
                // Branch doesn't exist; create it with an initial version of v1.0.0
                $version = 'v1.0.0';
            }

            // Checkout to a new temporary branch
            shell_exec("git checkout -B temp_$branch $main_branch");

            // Remove all files except the folder of interest
            foreach ( $branches as $_branch ) {
                if ( $branch != $_branch ) {
                    delete_recursive($_branch);
                }
            }

            // Commit changes
            shell_exec("git add .");
            shell_exec("git commit -m '$version'");

            // Tag this commit
            shell_exec("git tag $version");

            // Force move the branch pointer to the current commit
            shell_exec("git branch -f $branch");
            shell_exec("git checkout $branch");

            //delete temp branch
            shell_exec("git branch -D temp_$branch");

            // Push changes to remote
            shell_exec("git push origin $branch --force");
            shell_exec("git push origin $version");

            // Checkout to the base branch before processing the next folder
            shell_exec("git checkout $main_branch");
        }
    }

    private static function increment_version($version) {
        list($major, $minor, $patch) = explode('.', $version);
        $patch++;
        return "$major.$minor.$patch";
    }
}
