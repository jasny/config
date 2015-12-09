all:
	echo "Nothing to do"

supervisord: venv
	./venv/bin/supervisord

venv:
	virtualenv venv && . ./venv/bin/activate && pip install -r requirements.txt

dynalite:
	./venv/bin/supervisorctl start dynalite

dynamodb_local:
	./venv/bin/supervisorctl start dynamodb_local

stop:
	./venv/bin/supervisorctl stop all
	kill `cat supervisord.pid`

venvclean:
	rm -rf venv

dynamodblocalclean:
	rm -rf dynamodb_local

supervisordclean:
	rm -f supervisord.log

cleanall: venvclean dynamodblocalclean supervisordclean