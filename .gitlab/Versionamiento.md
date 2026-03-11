
## Tag de versión (actualmente v8.2.0)

```bash
v1.4.2
 │ │ └── PATCH: bug fixes, sin romper nada
 │ └──── MINOR: nueva funcionalidad, compatible
 └────── MAJOR: cambios que rompen compatibilidad
```

## Commit donde se soluciona un error

```bash
git commit -n "fix(module) : Bug"
                    │         │
                    │         └────── Mensaje descriptivo sobre la solución que hace el commit
                    └────── Modulo afectado de la solución de los bugs

git tag v8.2.{x}
git push origin main
git push origin v8.2.{x}
```

## Commit que tiene una nueva funcionalidad

```bash
git commit -n "feat(module) : Feature"
                    │           │
                    │           └────── Mensaje descriptivo de la nueva característica que se ha agregado
                    └────── Modulo afectado de la solución de los bugs

git tag -a v8.{y}.{x} -m "Release v8.{y}.{x} {Descripción sobre la nueva release}"

git push origin main
git push origin v8.{y}.{x}
```

Esto se realizaría cada mes donde se crearia un nuevo tag con su comentario adicional sobre las nuevas característica, tiene que seguir la estructura de los commits para diferenciar los bugs con las features