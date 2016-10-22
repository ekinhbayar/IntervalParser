# How to contribute

:tada: First off, thanks for taking the time to contribute! :tada:

Contributing should be as easy as possible for anyone but there are a few things to keep in mind.
The following guidelines for contribution should be followed if you want to submit a pull request. 

## Coding Style
* Make use of provided .editorconfig file.
* Make sure to have End of File new lines.
* Use 4 spaces for indentation.
* Make use of docblocks and comments.

## How to prepare

* You need a [GitHub account](https://github.com/signup/free)
* Submit an [issue](https://github.com/anselmh/CONTRIBUTING.md/issues) if there isn't one for the same matter yet.
	* Describe the issue and include steps to reproduce if it's a bug.
	* Ensure to mention the earliest version that you know is affected.
* If you are able and want to fix this, fork the repository on GitHub

## Make Changes

* In your forked repository, create a topic branch for your upcoming patch. (e.g. `feature--name`)
	* Usually this is based on the master branch.
	* Create a branch based on master; `git branch
	fix/master/my_contribution master` then checkout the new branch with `git
	checkout fix/master/my_contribution`.  Please avoid working directly on the `master` branch.
* Make sure you stick to the coding style that is used already.
* Make use of the `.editorconfig`-file if provided with the repository.
* Make commits of logical units and describe them properly.
* Check for unnecessary whitespace with `git diff --check` before committing.

* If possible, submit tests to your patch / new feature so it can be tested easily.
* Assure nothing is broken by running all the tests.

## Submit Changes

* Push your changes to a topic branch in your fork of the repository.
* Open a pull request to the original repository and choose the right original branch you want to patch.
	_Advanced users may install the `hub` gem and use the [`hub pull-request` command](https://hub.github.com/hub.1.html)._
* If not done in commit messages (which you really should do) please reference and update your issue with the code changes. But _please do not close the issue yourself_.
_Notice: You can [turn your previously filed issues into a pull-request here](http://issue2pr.herokuapp.com/)._

# Additional Resources

* [General GitHub documentation](http://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/articles/about-pull-requests/)
