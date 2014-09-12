#!/bin/bash

echo

DIR=~/dotfiles
FILES=".bash_aliases .inputrc"

# echo "Creating dotfiles folder at Root..."
# mkdir -v $DIR
# echo "done!"

# echo -n "Going into dotfiles"
# cd $DIR
# echo $PWD

# if [ ! -a ~/.inputrc ]; then 
#   echo "\$include /etc/inputrc" > ~/.inputrc
# fi

#Symlink Files
for file in $FILES; do
  echo "Symlinking dotfiles..."
  ln -sv $DIR/$file ~/$file
  echo "Done!"
done

echo