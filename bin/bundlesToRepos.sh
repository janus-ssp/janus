#!/bin/bash
# Note this script requires gitsubtree: http://engineeredweb.com/blog/how-to-install-git-subtree/

CURRENT_DIR=$(pwd);

# Create import dir
JANUS_DIR="$CURRENT_DIR"
BUNDLES_PATH="src/Janus/ServiceRegistry/Bundle"

function createFilteredBundle {
    BUNDLE_NAME=$1
    BUNDLE_PATH=$BUNDLES_PATH/$BUNDLE_NAME
    BUNDLE_EXPORT_DIR="$CURRENT_DIR/vendor/janus-ssp/janus/$BUNDLE_PATH"
    echo "Filtering bundle $BUNDLE_PATH to $BUNDLE_EXPORT_DIR"

    git subtree split -P src -b $BUNDLE_NAME

    echo "Created a filtered export of bundle only"
    #Create the new repo
    rm -rf $BUNDLE_EXPORT_DIR
    mkdir -p $BUNDLE_EXPORT_DIR
    cd $BUNDLE_EXPORT_DIR
    git init
    git fetch $JANUS_DIR $BUNDLE_NAME
    git checkout -b master FETCH_HEAD
    git mv Janus/ServiceRegistry/Bundle/$BUNDLE_NAME/* .
    rm -rf Janus
    rm .htaccess
    git add *
    git commit -m "Split of bundle from Janus main repo"
    cp $JANUS_DIR/composer.json .

    # Link the new repo to Github or wherever
    #git remote add origin <git@github.com:janus-ssp/ServiceRegistry$BUNDLE_NAME.git>
    #git push origin -u master

    # Remove tmp branch
    cd $JANUS_DIR
    git branch -D $BUNDLE_NAME

    #Cleanup, if desired
    #git rm -rf <name-of-folder>
}

cd $JANUS_DIR
for bundlePath in `ls $BUNDLES_PATH`
do
 createFilteredBundle $bundlePath
done