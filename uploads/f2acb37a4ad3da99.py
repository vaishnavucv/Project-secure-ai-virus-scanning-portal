import sys
import os
import vlc
from PyQt5.QtWidgets import QApplication, QWidget, QVBoxLayout
from PyQt5.QtCore import QTimer

class VideoPlayer(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("YouTube Video Player")
        self.setGeometry(100, 100, 900, 600)

        # VLC install directory
        vlc_path = r"C:\Program Files\VideoLAN\VLC"
        os.environ['PATH'] = vlc_path + os.pathsep + os.environ['PATH']

        # VLC instance
        self.instance = vlc.Instance('--no-xlib', '--quiet')
        self.media_player = self.instance.media_player_new()

        layout = QVBoxLayout()
        self.setLayout(layout)

        # Set video window handle
        if sys.platform == "win32":
            self.media_player.set_hwnd(int(self.winId()))

        # Start playing after short delay (GUI must be fully shown)
        QTimer.singleShot(1000, self.play_video)

    def play_video(self):
        youtube_url = "https://www.youtube.com/watch?v=SBbAcXlmfe0"
        media = self.instance.media_new(youtube_url)
        self.media_player.set_media(media)
        self.media_player.play()

if __name__ == '__main__':
    app = QApplication(sys.argv)
    player = VideoPlayer()
    player.show()
    sys.exit(app.exec_())
