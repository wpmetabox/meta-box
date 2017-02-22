svn co -q "https://plugins.svn.wordpress.org/meta-box/trunk" svn
cp svn/.svn . -r
rm -rf svn
svn cleanup
svn stat | grep '^?' | awk '{print $2}' | xargs -d "\n" svn add
svn stat | grep '^!' | awk '{print $2}' | xargs -d "\n" svn rm --force
svn ci --no-auth-cache --username rilwis --password $WP_ORG_PASSWORD -m "Version $npm_package_version"
svn copy https://plugins.svn.wordpress.org/meta-box/trunk https://plugins.svn.wordpress.org/meta-box/tags/$npm_package_version -m "Version $npm_package_version"
