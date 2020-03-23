export AWS_ACCESS_KEY_ID := foo
export AWS_SECRET_ACCESS_KEY := bar
export AWS_CSM_ENABLED := false
export AWS_REGION := current
export AWS_BUCKET := default

tests:
	bin/phpunit
	bin/phpspec run
