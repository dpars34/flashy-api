rsync -avz --exclude='.env' --exclude='firebase_credentials.json' -e "ssh" /Users/daniel/Documents/Projects/flashy-app/flashy-api flashy-dev:/home/ec2-user/
