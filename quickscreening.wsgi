import sys
import logging
import os

# Set project base directory
project_home = '/var/www/quickscreening'

# Add project to system path
if project_home not in sys.path:
    sys.path.insert(0, project_home)

# Set environment variable
os.environ['FLASK_APP'] = 'app.py'

# Import and run the app
from app import app as application  # this line is important for mod_wsgi
