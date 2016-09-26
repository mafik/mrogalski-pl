#include <cstdio>
#include <map>
#include <set>
#include <vector>
#include <queue>

using namespace std;

struct block {
	int width;
	int height;
	block(int w, int h): width(w), height(h) {}
	bool operator <(const block& other) const {
		if(width != other.width) return width < other.width;
		return height < other.height;
	}
};

struct game;

struct state {
	block*** data;
	game* g;

	state();
	state(const state&);
	state(game*);
	~state();
	vector<state*> moves();
	void show();
	bool is_free(int col, int row);
	bool operator<(const state&) const;
	bool operator==(const state&) const;
	bool operator!=(const state&) const;
};

struct game {
	state* initial_state;
	map<state, state> state_tree;
	int width;
	int height;

	set<block*> blocks;
	block* target;
	int target_column, target_row;

	game(int w, int h): width(w), height(h) {
		initial_state = new state(this);
	}

	~game() {
		for(block* b : blocks) {
			delete b;
		}
		delete initial_state;
	}

	block* place_obstacle(int column, int row, int width, int height) {
		block* n = new block(width, height);
		blocks.insert(n);
		initial_state->data[column][row] = n;
		return n;
	}

	block* place_target(int column, int row, int width, int height, int tc, int tr) {
		target = place_obstacle(column, row, width, height);
		target_column = tc;
		target_row = tr;
	}

	void solve() {
		state_tree.insert(make_pair(*initial_state, *initial_state));
		queue<state*> q;
		state* answer;

		q.push(initial_state);

		int i = 0;
		while(!q.empty()) {

			if(++i % 10000 == 0)
				printf("Runda %d: %lu stanów w kolejce\n", i, q.size());
			state* source = q.front(); q.pop();
			//source->show();
			vector<state*> next_v = source->moves();
			for(state* current : next_v) {
				if(state_tree.find(*current) == state_tree.end()) {
					state_tree.insert(make_pair(*current, *source));
					q.push(current);
					//printf("Dodaję nowy stan do kolejki!\n");
					//current->show();
				} else {
					//printf("Powrót do wcześniejszego stanu!!!\n");
					//current->show();
					//printf("vvvv\n");
					//state_tree[*current].show();
				}
			}
			if(q.size() > 800) {
				//break;
			}
			/*if(i % 50000 == 10) {
				printf("Current state so far:\n");
				source->show();
			}*/
			if(source->data[target_column][target_row] == target) {
				answer = source;
				printf("Bingo!\n");
				break;
			}
		}

		state* t = answer;
		while(*t != *initial_state) {
			t->show();
			printf("----\n");
			t = &state_tree[*t];
		}

	}
};

state::state(): g(0), data(0) {}

state::state(game* _g) {
	g = _g;
	data = new block**[g->width];
	for(int column = 0; column < g->width; ++column) {
		data[column] = new block*[g->height];
		for(int row = 0; row < g->height; ++row) {
			data[column][row] = 0;
		}
	}
}

state::state(const state& orig) {
	g = orig.g;
	data = new block**[g->width];
	for(int column = 0; column < g->width; ++column) {
		data[column] = new block*[g->height];
		for(int row = 0; row < g->height; ++row) {
			data[column][row] = orig.data[column][row];
		}
	}
}

state::~state() {
	if(data) {
		for(int column = 0; column < g->width; ++column) {
			if(data[column])
				delete[] data[column];
		}
		delete[] data;
	}
}

bool state::operator <(const state& other) const {
	if(g != other.g) return g < other.g;
	for(int column = 0; column < g->width; ++column) {
		for(int row = 0; row < g->height; ++row) {
			/*if(data[column][row] != other.data[column][row]) {
				return data[column][row] < other.data[column][row];
			}*/
			
			if(data[column][row] == 0 and other.data[column][row] == 0) {
				continue;
			}
			if(data[column][row] == 0) {
				return false;
			}
			if(other.data[column][row] == 0) {
				return true;
			}
			if((*data[column][row]) < (*other.data[column][row])) {
				return true;
			} else if((*other.data[column][row]) < (*data[column][row])) {
				return false;
			}
			
		}
	}
	return false;
}

bool state::operator !=(const state& other) const {
	return *this < other or other < *this;
}

bool state::operator ==(const state& other) const {
	return !(*this != other);
}


bool state::is_free(int C, int R) {
	for(int column = 0; column <= C; ++column) {
		for(int row = 0; row <= R; ++row) {
			if(data[column][row] &&
				column + data[column][row]->width > C &&
				row + data[column][row]->height > R) {
				return false;
			}
		}
	}
	return true;
}

vector<state*> state::moves() {
	vector<state*> ret;

	for(int column = 0; column < g->width; ++column) {
		for(int row = 0; row < g->height; ++row) {
			if(data[column][row]) {
				int left = column;
				int right = column + data[column][row]->width - 1;
				int top = row;
				int bottom = row + data[column][row]->height - 1;

				if(bottom < g->height - 1) { // sprawdzić ruch w dół
					bool allowed = true;
					for(int ic = left; ic <= right; ++ic) {
						if(is_free(ic, bottom + 1) == false) {
							allowed = false;
							break;
						}
					}
					if(allowed) {
						state* ns = new state(*this);
						swap(ns->data[column][row + 1], ns->data[column][row]);
						ret.push_back(ns);
					}
				}
				if(top > 0) { // sprawdzić ruch w górę
					bool allowed = true;
					for(int ic = left; ic <= right; ++ic) {
						if(is_free(ic, top - 1) == false) {
							allowed = false;
							break;
						}
					}
					if(allowed) {
						state* ns = new state(*this);
						swap(ns->data[column][row - 1], ns->data[column][row]);
						ret.push_back(ns);
					}
				}
				if(left > 0) { // sprawdzić ruch w lewo
					bool allowed = true;
					for(int ir = top; ir <= bottom; ++ir) {
						if(is_free(left - 1, ir) == false) {
							allowed = false;
							break;
						}
					}
					if(allowed) {
						state* ns = new state(*this);
						swap(ns->data[column - 1][row], ns->data[column][row]);
						ret.push_back(ns);
					}
				}
				if(right < g->width - 1) { // sprawdzić ruch w prawo
					bool allowed = true;
					for(int ir = top; ir <= bottom; ++ir) {
						if(is_free(right + 1, ir) == false) {
							allowed = false;
							break;
						}
					}
					if(allowed) {
						state* ns = new state(*this);
						swap(ns->data[column + 1][row], ns->data[column][row]);
						ret.push_back(ns);
					}
				}
			}
		}
	}

	return ret;
}

/* canvas + console = convas */
struct convas {
	char** buff;
	int width, height;

	convas(int width, int height): width(width), height(height) {
		buff = new char*[height];
		for(int row = 0; row < height; ++row) {
			buff[row] = new char[width];
			for(int col = 0; col < width; ++col) {
				buff[row][col] = ' ';
			}
		}
	}

	~convas() {
		if(buff) {
			for(int i=0; i < height; ++i) {
				delete[] buff[i];
			}
			delete[] buff;
		}
	}

	void rect(int x, int y, int w, int h) {
		for(int c = x + 1; c < x+w-1; ++c) {
			buff[y][c] = '-';
			buff[y+h-1][c] = '-';
		}

		buff[y+h-1][x+w-1] = '/';
		buff[y][x] = '/';
		buff[y][x+w-1] = '\\';
		buff[y+h-1][x] = '\\';

		for(int r = y + 1; r < y+h-1; ++r) {
			buff[r][x] = '|';
			buff[r][x+w-1] = '|';
		}

		for(int r = y + 1; r < y+h-1; ++r) {
			for(int c = x + 1; c < x+w-1; ++c) {
				buff[r][c] = '#';
			}
		}
	}

	void print() {
		for(int row = 0; row < height; ++row) {
			for(int column = 0; column < width; ++column) {
				putchar(buff[row][column]);
			}
			putchar('\n');
		}	
	}
};

void state::show() {
	int xscale = 3, yscale = 2;
	convas c(g->width * xscale, g->height * yscale);

	for(int column = 0; column < g->width; ++column) {
		for(int row = 0; row < g->height; ++row) {
			block* b = data[column][row];
			if(b) {
				c.rect(column * xscale, row * yscale, b->width * xscale, b->height * yscale);
			}
		}
	}

	c.print();
}

int main() {
	game b(4, 5);
	b.place_obstacle(0, 0, 1, 2);
	b.place_obstacle(3, 0, 1, 2);
	b.place_obstacle(0, 2, 1, 1);
	b.place_obstacle(1, 2, 1, 1);
	b.place_obstacle(2, 2, 1, 1);
	b.place_obstacle(3, 2, 1, 1);
	b.place_obstacle(0, 3, 2, 1);
	b.place_obstacle(2, 3, 2, 1);
	b.place_obstacle(0, 4, 1, 1);
	b.place_obstacle(3, 4, 1, 1);
	b.place_target(1, 0, 2, 2, 1, 3);

  	printf("Zaczynam przeszukiwanie...\n");

  	b.solve();
}