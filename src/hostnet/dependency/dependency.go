package dependency

import (
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"../resolve"
)

type regex struct {
	re_less *regexp.Regexp
	re_ts *regexp.Regexp
	re_js *regexp.Regexp
	re_boundry *regexp.Regexp
}

type File struct {
	Name string
	File string
	Import string
}

type FileContent struct {
	Meta File
	Content []byte
}

func In(needle File, total ...[]File) bool {
	for _, list := range total {
		for _, b := range list {
			if b.File == needle.File {
				return true
			}
		}
	}
	return false
}

var regex_init = regex{
	re_less: regexp.MustCompile(`@import (\([a-z,\s]*\)\s*)?(url\()?('([^']+)'|"([^"]+)")`),
	re_ts: regexp.MustCompile(`import(.*from)?\s+["'](.*)["'];`),
	re_js: regexp.MustCompile(`(.?)require\(([']([^']+)[']|["]([^"]+)["])\)`),
	re_boundry: regexp.MustCompile(`[a-zA-Z_0-9.]`),
}

func Less(file FileContent) []File {
	matches := regex_init.re_less.FindAllStringSubmatch(string(file.Content), -1)

	result := []File{}
	cwd := filepath.Dir(file.Meta.File)

	for _, m := range matches {
		path := m[4]
		if len(path) == 0 {
			path = m[5]
		}

		// there can be :// which indicates a transport protocol, it (should) never be to a file.
		if strings.Contains(path, "://") {
			continue
		}

		ext := filepath.Ext(path)
		module_name := path

		if "" == ext {
			module_name = module_name + ".less"
		}

		result = append(result, File{Name: module_name, File: filepath.Clean(cwd + string(os.PathSeparator) + module_name), Import: path})
	}

	return result
}

func Ts(file FileContent) []File {
	matches := regex_init.re_ts.FindAllStringSubmatch(string(file.Content), -1)

	exts := []string {".ts", ".d.ts"}
	// First get all the regular requires
	result := Js(file, exts, false)
	cwd := filepath.Dir(file.Meta.File)

	for _, m := range matches {
		file, e := resolve.File(m[2], cwd, exts)

		if e {
			continue
		}

		result = append(result, File{Name: m[2], File: file, Import: m[2]})
	}

	return result
}

func Js(file FileContent, ext []string, raw bool) []File {
	matches := regex_init.re_js.FindAllStringSubmatch(string(file.Content), -1)

	result := []File{}
	cwd := filepath.Dir(file.Meta.File)

	for _, m := range matches {
		path := m[3]
		if len(path) == 0 {
			path = m[4]
		}

		// do we have a valid require?
		if regex_init.re_boundry.MatchString(m[1]) {
			continue
		}

		file, e := resolve.File(path, cwd, ext)

		if e {
			continue
		}

		module_name := path

		if !raw && (module_name[0] == '.' && (module_name[1] == '/' || (module_name[1] == '.' && module_name[2] == '/'))) {
			module_name = filepath.Clean(cwd + "/" + module_name)
		}

		result = append(result, File{Name: module_name, File: file, Import: path})
	}

	return result
}