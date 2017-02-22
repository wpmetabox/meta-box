svn stat | grep '^?' | awk '{print $2}' | xargs -d "\n" svn add
svn stat | grep '^!' | awk '{print $2}' | xargs -d "\n" svn rm --force
svn ci --no-auth-cache --username rilwis -m "Version $npm_package_version"
svn copy https://plugins.svn.wordpress.org/meta-box/trunk https://plugins.svn.wordpress.org/meta-box/tags/$npm_package_version -m "Version $npm_package_version"
