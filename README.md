![Art Institute of Chicago](https://raw.githubusercontent.com/Art-Institute-of-Chicago/template/master/aic-logo.gif)

# Data Service Template
> A cloneable repo containing examples and architecture for new dataservices

This repo is meant to be cloned whenever we need to create a new dataservice for our data hub. It was derived from existing dataservices, with a focus towards simplicity and reliability.



## Instructions

For convenience, we recommend appending the following function to your `~/.bash_profile` or `~/.bashrc`:

```bash
ds() {

    ORG="art-institute-of-chicago"

    case "$1" in

        "create")

            NAME="$2"

            if [ -z "$NAME" ]; then
                echo 'You must specify a dataservice name, e.g. `images` for `data-service-images`: ds create images'
                return 1
            fi

            git clone "https://github.com/$ORG/data-service-template.git" "data-service-$NAME"

            cd "data-service-$NAME"

            git remote set-url origin "https://github.com/$ORG/data-service-$NAME.git"

            git fetch

            composer install

            # Uncomment this command if you'd like to open the repo in Sublime Text
            # subl .

            curl -s "https://api.github.com/repos/$ORG/data-service-$NAME" 2>&1 | grep -q 'Not Found'

            if [ $? -eq 0 ]; then
                echo "Local repo is initialized, but there's no matching remote repo on GitHub!"
            fi

        ;;

        "squash")

            HASH="$(git log --pretty=format:'%h' -n 1)"

            MSG="$(printf "%s\n\n" \
                'Add initial boilerplate' \
                'Squashed from `data-service-template` at the following commit:' \
                "https://github.com/$ORG/data-service-template/commit/$HASH")";

            git reset 8d79a4e
            git add --all
            git commit -m "$MSG"

        ;;

        "clean")

            rm 'app/Bar.php'
            rm 'app/Foo.php'
            rm 'app/Http/Controllers/BarController.php'
            rm 'app/Http/Controllers/FooController.php'
            rm 'app/Http/Transformers/BarTransformer.php'
            rm 'app/Http/Transformers/FooTransformer.php'
            rm 'database/factories/BarFactory.php'
            rm 'database/factories/FooFactory.php'
            rm 'database/migrations/2018_02_26_130000_foo_bar.php'

            # TODO: Remove `foos` and `bars` lines from routes/api.php

        ;;

        *)
            echo "You must specify an action: create <name>, squash, clean"
        ;;

    esac

}
```

Now, whenever you need to create a new dataservice, you can do the following:

1. Create a new `data-service-foobar` repo on GitHub.

1. Navigate to the parent directory of wherever you want to create the dataservice (e.g. `/var/www`).

1. Run the following command, replacing `foobar` with your dataservice's name:

```bash
ds create foobar
```

The idea is to clone this repo into a new directory, and replace its `origin` remote with the new dataservice repo on GitHub. In the process, we remove its reference to `data-service-template`.

If desired, you can also squash its history. The function above does it while leaving the initial commit intact:

```bash
# Run this inside the local repo dir
ds squash
```

Unfortunately, we cannot use `composer create-project` in this context.

## Contributing

We encourage your contributions. Please fork this repository and make your changes in a separate branch. To better understand how we organize our code, please review our [version control guidelines](https://docs.google.com/document/d/1B-27HBUc6LDYHwvxp3ILUcPTo67VFIGwo5Hiq4J9Jjw).

```bash
# Clone the repo to your computer
git clone git@github.com:your-github-account/data-service-template.git

# Enter the folder that was created by the clone
cd data-service-template

# Start a feature branch
git checkout -b feature/good-short-description

# ... make some changes, commit your code

# Push your branch to GitHub
git push origin feature/good-short-description
```

Then on github.com, create a Pull Request to merge your changes into our
`develop` branch.

This project is released with a Contributor Code of Conduct. By participating in
this project you agree to abide by its [terms](CODE_OF_CONDUCT.md).

We welcome bug reports and questions under GitHub's [Issues](issues). For other concerns, you can reach our engineering team at [engineering@artic.edu](mailto:engineering@artic.edu)

## Licensing

This project is licensed under the [GNU Affero General Public License
Version 3](LICENSE).
