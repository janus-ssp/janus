Creating a release
------------------

Janus has built in support for creating a release. The created releases are meant to create a version of Janus which works as a plugin for SimpleSamlPhp

Creating a release is as simple as calling
```sh
cd bin
sh ./RMT release
```

The tool will then asked a series of questions and create a release in the releases dir.

The tool behaves differently depending on which branch it is called from. While the tool is meant to make an official release from master in the first place it's also possible to make releases of other branches.

When making a release from master the following happens:
* Check if working copy is clean
* Check if unittests can be runned succesfully
* Update the changelog
* Create a tag
* Push tag to github
* Create an archive in the releases dir suffixed with the tag name
* Create an archive in the releases dir suffixed with the tag name

When making a release from a branch other than master the following happens:
* Check if working copy is clean
* Check if unittests can be runned succesfully
* Update the changelog
* Create an archive in the releases dir suffixed with the branch name and commit hash
