# Set up Sublime Preferences

DIR=~/dotfiles/sublime-text-3
SUBLDIR=~/.config/sublime-text-3/Packages/User/
PACKAGE="$SUBLDIR/Package Control.sublime-settings"

if [ -f "$PACKAGE" ]; then
  echo "Symlinking Sublime Text Preferences together..."
  ln -sfv $DIR/Package\ Control.sublime-settings $SUBLDIR
  ln -sfv $DIR/Preferences.sublime-settings $SUBLDIR
  echo "done!"
else
  echo "Sublime Text 3 packages file doesn't exist, please install Package Control first!"
fi